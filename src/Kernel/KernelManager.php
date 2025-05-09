<?php

declare(strict_types=1);

namespace App\Kernel;

use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Logging\Application\LogEntryAssemblerInterface;
use App\Infrastructure\Logging\LoggerInterface;
use App\Kernel\Application\UseCaseKernel;
use App\Kernel\Infrastructure\Database\DatabaseConnectionKernel;
use App\Kernel\Infrastructure\Database\DatabaseExecutionKernel;
use App\Kernel\Infrastructure\InfrastructureKernel;
use App\Kernel\Infrastructure\LoggingKernel;
use App\Kernel\Infrastructure\RouterKernel;
use App\Kernel\Presentation\ControllerKernel;
use Config\Container\ConfigContainer;
use Config\Database\DatabaseSchemaConfig;

/**
 * KernelManager
 *
 * Centralizes initialization and access to all application-level kernels.
 * This class ensures correct construction order and dependency wiring.
 */
final class KernelManager
{
    private ConfigContainer $configContainer;
    private LoggingKernel $loggingKernel;
    private InfrastructureKernel $infrastructureKernel;
    private DatabaseConnectionKernel $databaseConnectionKernel;
    private DatabaseExecutionKernel $databaseExecutionKernel;
    private UseCaseKernel $useCaseKernel;
    private ControllerKernel $controllerKernel;
    private RouterKernel $routerKernel;
    private LoggerInterface $logger;
    private LogEntryAssemblerInterface $logEntryAssembler;
    private DatabaseConnectionInterface $connection;

    /**
     * 
     *
     * @param ConfigContainer $config Fully initialized configuration container.
     */
    public function __construct(ConfigContainer $configContainer)
    {
        $this->configContainer = $configContainer;
    }

    /**
     * Initializes and wires all kernels based on the application configuration.
     */
    public function boot(): void
    {
        $this->initializeLogging();
        $this->initializeInfrastructure();
        $this->initializeDatabaseConnection();
        $this->initializeDatabaseExecution();
        $this->initializeUseCase();
        $this->initializeControllers();
        $this->initializeRouter();
    }

    private function initializeLogging(): void
    {
        $this->loggingKernel = new LoggingKernel($this->configContainer);
        $this->logger = $this->loggingKernel->getLogger();
        $this->logEntryAssembler = $this->loggingKernel->getLogEntryAssembler();
    }

    private function initializeInfrastructure(): void
    {
        $this->infrastructureKernel = new InfrastructureKernel($this->configContainer, $this->loggingKernel);
    }

    private function initializeDatabaseConnection(): void
    {
        $databaseConfig = $this->configContainer->getDatabaseConfig();
        $this->databaseConnectionKernel = new DatabaseConnectionKernel($databaseConfig, $this->logger);
        $this->connection = $this->databaseConnectionKernel()->getConnection();
    }

    private function initializeDatabaseExecution(): void
    {
        $this->databaseExecutionKernel = new DatabaseExecutionKernel($this->connection, $this->logger);
    }

    private function initializeUseCase(): void
    {
        $this->useCaseKernel = new UseCaseKernel($this->connection, $this->logger);
    }

    private function initializeControllers(): void
    {
        $this->controllerKernel = new ControllerKernel(
            $this->configContainer,
            $this->infrastructureKernel->getSessionHandler(),
            $this->infrastructureKernel->getViewRenderer(),
            $this->infrastructureKernel->getUrlResolver(),
            $this->infrastructureKernel->getLogger(),
            $this->infrastructureKernel->getLogEntryAssembler(),
            $this->useCaseKernel->list(),
            $this->useCaseKernel->create(),
            $this->useCaseKernel->update(),
            $this->useCaseKernel->delete()
        );
    }

    private function initializeRouter(): void
    {
        $this->routerKernel = new RouterKernel($this->controllerKernel->map());
    }

    public function infrastructureKernel(): InfrastructureKernel
    {
        return $this->infrastructureKernel;
    }

    public function databaseConnectionKernel(): DatabaseConnectionKernel
    {
        return $this->databaseConnectionKernel;
    }

    public function useCaseKernel(): UseCaseKernel
    {
        return $this->useCaseKernel;
    }

    public function controllerKernel(): ControllerKernel
    {
        return $this->controllerKernel;
    }

    public function routerKernel(): RouterKernel
    {
        return $this->routerKernel;
    }
}
