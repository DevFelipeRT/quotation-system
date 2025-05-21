<?php

declare(strict_types=1);

namespace App\Kernel;

use App\Kernel\Application\UseCaseKernel;
use App\Kernel\Infrastructure\Database\DatabaseConnectionKernel;
use App\Kernel\Infrastructure\Database\DatabaseExecutionKernel;
use App\Kernel\Infrastructure\LoggingKernel;
use App\Kernel\Infrastructure\RouterKernel;
use App\Kernel\Infrastructure\SessionKernel;
use App\Kernel\Presentation\ControllerKernel;
use App\Kernel\Adapters\EventListening\EventListeningKernel;
use App\Kernel\Container\ContainerKernel;
use App\Shared\Container\AppContainerInterface;
use App\Infrastructure\Rendering\Infrastructure\HtmlViewRenderer;
use App\Infrastructure\Http\AppUrlResolver;
use App\Infrastructure\Logging\Application\LogEntryAssembler;
use App\Infrastructure\Logging\Infrastructure\Contracts\LoggerInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;
use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use Config\ConfigProvider;
use Config\Kernel\KernelConfig;
use Throwable;

/**
 * KernelManager
 *
 * Orchestrates all core infrastructure and service kernels, enforcing
 * module criticity policies defined in KernelConfig.
 * All steps are strictly separated and all failures are handled explicitly.
 */
final class KernelManager
{
    private KernelConfig $kernelConfig;
    // Kernel instances
    private LoggingKernel $loggingKernel;
    private ContainerKernel $containerKernel;
    private EventListeningKernel $eventListeningKernel;
    private DatabaseConnectionKernel $databaseConnectionKernel;
    private DatabaseExecutionKernel $databaseExecutionKernel;
    private SessionKernel $sessionKernel;
    private RouterKernel $routerKernel;
    private UseCaseKernel $useCaseKernel;
    private ControllerKernel $controllerKernel;
    // Module instances
    private LoggerInterface $logger;
    private PsrLoggerInterface $psrLogger;
    private AppContainerInterface $container;
    private EventDispatcherInterface $eventDispatcher;
    private DatabaseConnectionInterface $dbConnection;

    /** @var array<string, Throwable> */
    private array $moduleFailures = [];

    /**
     * @throws Throwable
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->kernelConfig = $configProvider->getKernelConfig();

        $this->initializeLoggingKernel($configProvider);
        $this->initializeContainerKernel($configProvider);
        $this->initializeEventListeningKernel();
        $this->initializeDatabaseConnectionKernel($configProvider);
        $this->initializeDatabaseExecutionKernel();
        $this->initializeAdditionalKernels($configProvider);
    }

    /**
     * Step 1: Logging kernel is always critical.
     */
    private function initializeLoggingKernel(ConfigProvider $configProvider): void
    {
        try {
            $this->loggingKernel = new LoggingKernel($configProvider);
            $this->logger = $this->loggingKernel->getLogger();
            $this->psrLogger = $this->loggingKernel->getPsrLogger();
        } catch (Throwable $e) {
            $this->handleModuleFailure('logger', $e, true);
        }
    }

    /**
     * Step 2: Container is always critical.
     */
    private function initializeContainerKernel(): void
    {
        try {
            $this->containerKernel = new ContainerKernel();
            $this->container = $this->containerKernel->create($this->logger, $this->psrLogger);
        } catch (Throwable $e) {
            $this->handleModuleFailure('container', $e, true);
        }
        
    }

    /**
     * Step 3: Event dispatcher is always critical.
     */
    private function initializeEventListeningKernel(): void
    {
        try {
            $this->eventListeningKernel = new EventListeningKernel($this->container);
            $this->eventDispatcher = $this->eventListeningKernel->dispatcher();
        } catch (Throwable $e) {
            $this->handleModuleFailure('eventDispatcher', $e, true);
        }
    }

    /**
     * Step 4: Database connection kernel is always critical.
     */
    private function initializeDatabaseConnectionKernel(ConfigProvider $configProvider): void
    {
        try {
            $databaseConfig = $configProvider->getDatabaseConfig();
            $this->databaseConnectionKernel = new DatabaseConnectionKernel($databaseConfig, $this->eventDispatcher, true);
            $this->dbConnection = $this->databaseConnectionKernel->getConnection();
        } catch (Throwable $e) {
            $this->handleModuleFailure('database', $e, true);
        }
    }

    /**
     * Step 5: Database execution kernel is always critical.
     */
    private function initializeDatabaseExecutionKernel(): void
    {
        try {
            $this->databaseExecutionKernel = new DatabaseExecutionKernel($this->dbConnection, $this->eventDispatcher);
        } catch (Throwable $e) {
            $this->handleModuleFailure('databaseExecution', $e, true);
        }
    }

    /**
     * Step 6: Initialize all other (critical or not) kernels using KernelConfig.
     */
    private function initializeAdditionalKernels(ConfigProvider $configProvider): void
    {
        $sessionConfig = $configProvider->getSessionConfig();
        $this->initializeKernelModule('session', function () use ($sessionConfig) {
            $this->sessionKernel = new SessionKernel($sessionConfig, $this->eventDispatcher);
        });

        $this->initializeKernelModule('router', function () {
            $this->routerKernel = $this->createRouterKernel($this->eventDispatcher);
        });

        $this->initializeKernelModule('useCase', function () {
            $this->useCaseKernel = new UseCaseKernel($this->dbConnection, $this->logger);
        });

        $this->initializeKernelModule('controller', function () use ($configProvider) {
            $this->controllerKernel = $this->createControllerKernel(
                $configProvider,
                $this->sessionKernel ?? null,
                $this->logger
            );
        });
    }

    /**
     * Handles module initialization failures based on criticality.
     */
    private function handleModuleFailure(string $moduleName, Throwable $exception, bool $isCritical): void
    {
        $this->moduleFailures[$moduleName] = $exception;

        if (isset($this->psrLogger)) {
            $this->psrLogger->error(
                "[KernelManager] Module '{$moduleName}' failed: {$exception->getMessage()}",
                ['exception' => $exception]
            );
        } else {
            error_log("[KernelManager] Module '{$moduleName}' failed: {$exception->getMessage()}");
        }

        if ($isCritical) {
            throw $exception;
        }
    }

    /**
     * Initializes a kernel module with error handling policy.
     */
    private function initializeKernelModule(string $moduleName, callable $initializer): void
    {
        $isCritical = in_array($moduleName, $this->kernelConfig->getEssentialModules(), true);

        try {
            $initializer();
        } catch (Throwable $e) {
            $this->handleModuleFailure($moduleName, $e, $isCritical);
        }
    }

    private function createRouterKernel(EventDispatcherInterface $eventDispatcher): RouterKernel
    {
        $controllerMap = $this->getControllerMap();
        $controllerClassMap = $this->getControllerClassMap();

        return new RouterKernel($controllerMap, $controllerClassMap, $eventDispatcher);
    }

    private function createControllerKernel(
        ConfigProvider $configProvider,
        ?SessionKernel $sessionKernel,
        LoggerInterface $logger
    ): ControllerKernel {
        $viewRenderer = new HtmlViewRenderer();
        $pathsConfig = $configProvider->getPathsConfig();
        $urlResolver = new AppUrlResolver($pathsConfig->getAppDirectory());
        $logEntryAssembler = new LogEntryAssembler();

        $listUseCase   = $this->useCaseKernel?->getListUseCase();
        $createUseCase = $this->useCaseKernel?->getCreateUseCase();
        $updateUseCase = $this->useCaseKernel?->getUpdateUseCase();
        $deleteUseCase = $this->useCaseKernel?->getDeleteUseCase();

        return new ControllerKernel(
            $configProvider,
            $sessionKernel,
            $viewRenderer,
            $urlResolver,
            $logger,
            $logEntryAssembler,
            $listUseCase,
            $createUseCase,
            $updateUseCase,
            $deleteUseCase
        );
    }

    private function getControllerMap(): array
    {
        return [];
    }

    private function getControllerClassMap(): array
    {
        return [];
    }

    // ====== Kernel and Service Accessors ======

    public function getLoggingKernel(): LoggingKernel
    {
        return $this->loggingKernel;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getPsrLogger(): PsrLoggerInterface
    {
        return $this->psrLogger;
    }

    public function getEventListeningKernel(): EventListeningKernel
    {
        return $this->eventListeningKernel;
    }

    public function getDatabaseConnectionKernel(): DatabaseConnectionKernel
    {
        return $this->databaseConnectionKernel;
    }

    public function getDatabaseConnection(): DatabaseConnectionInterface
    {
        return $this->dbConnection;
    }

    public function getDatabaseExecutionKernel(): ?DatabaseExecutionKernel
    {
        return $this->databaseExecutionKernel ?? null;
    }

    public function getSessionKernel(): ?SessionKernel
    {
        return $this->sessionKernel ?? null;
    }

    public function getRouterKernel(): ?RouterKernel
    {
        return $this->routerKernel ?? null;
    }

    public function getUseCaseKernel(): ?UseCaseKernel
    {
        return $this->useCaseKernel ?? null;
    }

    public function getControllerKernel(): ?ControllerKernel
    {
        return $this->controllerKernel ?? null;
    }

    public function getContainer(): AppContainerInterface
    {
        return $this->container;
    }

    public function getContainerKernel(): ContainerKernel
    {
        return $this->containerKernel;
    }

    /**
     * Returns all failures encountered during kernel bootstrap.
     *
     * @return array<string, Throwable>
     */
    public function getModuleFailures(): array
    {
        return $this->moduleFailures;
    }
}
