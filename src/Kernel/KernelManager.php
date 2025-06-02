<?php

declare(strict_types=1);

namespace App\Kernel;

use App\Kernel\Application\UseCaseKernel;
use App\Kernel\Infrastructure\Database\DatabaseConnectionKernel;
use App\Kernel\Infrastructure\Database\DatabaseExecutionKernel;
use App\Kernel\Infrastructure\LoggingKernel;
use App\Kernel\Infrastructure\Routing\RoutingKernel;
use App\Kernel\Infrastructure\SessionKernel;
use App\Kernel\Presentation\ControllerKernel;
use App\Kernel\Adapters\EventListening\EventListeningKernel;
use App\Kernel\Container\ContainerKernel;
use App\Infrastructure\Rendering\Infrastructure\HtmlViewRenderer;
use App\Infrastructure\Logging\Infrastructure\Contracts\LoggerInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;
use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Session\Domain\Contracts\SessionHandlerInterface;
use App\Kernel\Discovery\DiscoveryKernel;
use App\Shared\Container\Domain\Contracts\ContainerInterface;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use App\Shared\UrlResolver\AppUrlResolver;
use App\Shared\UrlResolver\UrlResolverInterface;
use Config\App\AppConfig;
use Config\ConfigProvider;
use Config\Kernel\KernelConfig;
use Config\Paths\PathsConfig;
use Throwable;

final class KernelManager
{
    private ConfigProvider $configProvider;
    private KernelConfig $kernelConfig;
    private AppConfig $appConfig;
    private PathsConfig $pathsConfig;

    private LoggingKernel $loggingKernel;
    private ContainerKernel $containerKernel;
    private DiscoveryKernel $discoveryKernel;
    private EventListeningKernel $eventListeningKernel;
    private DatabaseConnectionKernel $databaseConnectionKernel;
    private DatabaseExecutionKernel $databaseExecutionKernel;
    private SessionKernel $sessionKernel;
    private RoutingKernel $routerKernel;
    private UseCaseKernel $useCaseKernel;
    private ControllerKernel $controllerKernel;

    private LoggerInterface $logger;
    private PsrLoggerInterface $psrLogger;
    private ContainerInterface $container;
    
    private EventDispatcherInterface $eventDispatcher;
    private DatabaseConnectionInterface $dbConnection;
    private UrlResolverInterface $urlResolver;
    

    private array $moduleFailures = [];

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
        $this->kernelConfig = $configProvider->getKernelConfig();
        $this->appConfig = $configProvider->getAppConfig();
        $this->pathsConfig = $configProvider->getPathsConfig();

        $this->urlResolver = new AppUrlResolver($this->pathsConfig->getAppDirectory());

        $this->bootModules();
    }

    private function bootModules()
    {
        $this->initializeKernelModule('LoggingKernel', function () {
            $this->initializeLoggingKernel($this->configProvider);
        });

        $this->initializeKernelModule('DiscoveryKernel', function () {
            $this->initializeDiscoveryKernel();
        });

        $this->initializeKernelModule('ContainerKernel', function () {
            $this->initializeContainerKernel();
        });

        $this->initializeKernelModule('EventListeningKernel', function () {
            $this->initializeEventListeningKernel();
        });

        $this->initializeKernelModule('DatabaseConnectionKernel', function () {
            $this->initializeDatabaseConnectionKernel($this->configProvider);
        });

        $this->initializeKernelModule('DatabaseExecutionKernel', function () {
            $this->initializeDatabaseExecutionKernel();
        });

        $this->initializeKernelModule('SessionKernel', function () {
            $this->initializeSessionKernel($this->configProvider);
        });
        $this->initializeRoutingKernel();
        // $this->initializeKernelModule('RoutingKernel', function () {
        //     $this->initializeRoutingKernel();
        // });

        $this->initializeKernelModule('UseCaseKernel', function () {
            $this->initializeUseCaseKernel();
        });

        $this->initializeKernelModule('ControllerKernel', function () {
            $this->initializeControllerKernel($this->configProvider);
        });
    }

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

    private function initializeDiscoveryKernel(): void
    {
        try {
            $this->discoveryKernel = new DiscoveryKernel(
                $this->appConfig->getPsr4Prefix(),
                $this->pathsConfig->getSourceDirectory()
            );
        } catch (Throwable $e) {
            $this->handleModuleFailure('discovery', $e, true);
        }
    }

    private function initializeContainerKernel(): void
    {
        try {
            $this->containerKernel = new ContainerKernel();
            $this->container = $this->containerKernel::create($this->logger, $this->psrLogger, $this->discoveryKernel);
            $this->container->singleton(KernelManager::class, fn () => $this);
        } catch (Throwable $e) {
            $this->handleModuleFailure('container', $e, true);
        }
    }

    private function initializeEventListeningKernel(): void
    {
        try {
            $this->eventListeningKernel = new EventListeningKernel($this->container, $this->discoveryKernel);
            $this->eventDispatcher = $this->eventListeningKernel->dispatcher();
            $this->container->singleton(EventDispatcherInterface::class, fn () => $this->eventDispatcher);
        } catch (Throwable $e) {
            $this->handleModuleFailure('eventDispatcher', $e, true);
        }
    }

    private function initializeDatabaseConnectionKernel(ConfigProvider $configProvider): void
    {
        try {
            $databaseConfig = $configProvider->getDatabaseConfig();
            $this->databaseConnectionKernel = new DatabaseConnectionKernel($databaseConfig, $this->eventDispatcher, true);
            $this->dbConnection = $this->databaseConnectionKernel->getConnection();
            $this->container->singleton(DatabaseConnectionInterface::class, fn () => $this->dbConnection);
        } catch (Throwable $e) {
            $this->handleModuleFailure('database', $e, true);
        }
    }

    private function initializeDatabaseExecutionKernel(): void
    {
        try {
            $this->databaseExecutionKernel = new DatabaseExecutionKernel($this->dbConnection, $this->eventDispatcher);
        } catch (Throwable $e) {
            $this->handleModuleFailure('databaseExecution', $e, true);
        }
    }

    private function initializeSessionKernel(ConfigProvider $configProvider): void
    {
        try {
            $sessionConfig = $configProvider->getSessionConfig();
            $this->sessionKernel = new SessionKernel($sessionConfig, $this->eventDispatcher);
            $this->container->singleton(SessionHandlerInterface::class, fn () => $this->sessionKernel);
        } catch (Throwable $e) {
            $this->handleModuleFailure('session', $e, true);
        }
    }

    private function initializeRoutingKernel(): void
    {
        try {
            $this->routerKernel = new RoutingKernel($this->container, $this->discoveryKernel, $this->eventDispatcher);
        } catch (Throwable $e) {
            $this->handleModuleFailure('router', $e, true);
        }
    }

    private function initializeUseCaseKernel(): void
    {
        try {
            $this->useCaseKernel = $this->useCaseKernel = $this->container->get(UseCaseKernel::class);
        } catch (Throwable $e) {
            $this->handleModuleFailure('useCase', $e, true);
        }
    }

    private function initializeControllerKernel(ConfigProvider $configProvider): void
    {
        try {
            $this->controllerKernel = $this->createControllerKernel(
                $configProvider,
                $this->sessionKernel
            );
        } catch (Throwable $e) {
            $this->handleModuleFailure('controller', $e, true);
        }
    }

    private function createControllerKernel(
        ConfigProvider $configProvider,
        SessionKernel $sessionKernel,
    ): ControllerKernel {
        $templatesPath = $this->pathsConfig->getTemplatesPath();
        $viewRenderer = new HtmlViewRenderer($templatesPath, $this->urlResolver);
        $pathsConfig = $configProvider->getPathsConfig();
        $urlResolver = new AppUrlResolver($pathsConfig->getAppDirectory());

        return new ControllerKernel(
            $sessionKernel,
            $viewRenderer,
            $urlResolver,
            $this->useCaseKernel
        );
    }

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

    private function initializeKernelModule(string $moduleName, callable $initializer): void
    {
        $isCritical = in_array($moduleName, $this->kernelConfig->getEssentialModules(), true);

        try {
            $initializer();
        } catch (Throwable $e) {
            $this->handleModuleFailure($moduleName, $e, $isCritical);
        }
    }

    public function getLoggingKernel(): LoggingKernel { return $this->loggingKernel; }
    public function getLogger(): LoggerInterface { return $this->logger; }
    public function getPsrLogger(): PsrLoggerInterface { return $this->psrLogger; }
    public function getEventListeningKernel(): EventListeningKernel { return $this->eventListeningKernel; }
    public function getDatabaseConnectionKernel(): DatabaseConnectionKernel { return $this->databaseConnectionKernel; }
    public function getDatabaseConnection(): DatabaseConnectionInterface { return $this->dbConnection; }
    public function getDatabaseExecutionKernel(): ?DatabaseExecutionKernel { return $this->databaseExecutionKernel ?? null; }
    public function getSessionKernel(): ?SessionKernel { return $this->sessionKernel ?? null; }
    public function getRoutingKernel(): ?RoutingKernel { return $this->routerKernel ?? null; }
    public function getUseCaseKernel(): ?UseCaseKernel { return $this->useCaseKernel ?? null; }
    public function getControllerKernel(): ?ControllerKernel { return $this->controllerKernel ?? null; }
    public function getContainer(): ContainerInterface { return $this->container; }
    public function getContainerKernel(): ContainerKernel { return $this->containerKernel; }
    public function getModuleFailures(): array { return $this->moduleFailures; }
}
