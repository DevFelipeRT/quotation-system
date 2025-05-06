<?php

namespace App\Kernel;

use App\Logging\LoggerInterface;
use Config\Container\ConfigContainer;

/**
 * KernelManager
 *
 * Centralizes initialization and access to all application-level kernels.
 * This class ensures correct construction order and dependency wiring.
 */
final class KernelManager
{
    private InfrastructureKernel $infrastructureKernel;
    private DatabaseKernel $databaseKernel;
    private UseCaseKernel $useCaseKernel;
    private ControllerKernel $controllerKernel;
    private RouterKernel $routerKernel;

    /**
     * Initializes and wires all kernels based on the application configuration.
     *
     * @param ConfigContainer $config Fully initialized configuration container.
     */
    public function __construct(ConfigContainer $config)
    {
        // Step 1: Core infrastructure
        $this->infrastructureKernel = new InfrastructureKernel($config);

        // Step 2: Database connection
        $this->databaseKernel = new DatabaseKernel(
            $config->getDatabaseConfig(),
            $this->infrastructureKernel->getLogger()
        );

        // Step 3: Use cases wired with repository
        $this->useCaseKernel = new UseCaseKernel(
            $this->databaseKernel->getConnection(),
            $this->infrastructureKernel->getLogger()
        );

        // Step 4: Controllers wired with services and use cases
        $this->controllerKernel = new ControllerKernel(
            $config,
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

        // Step 5: RouterKernel uses controller map
        $this->routerKernel = new RouterKernel($this->controllerKernel->map());
    }

    public function infrastructure(): InfrastructureKernel
    {
        return $this->infrastructureKernel;
    }

    public function database(): DatabaseKernel
    {
        return $this->databaseKernel;
    }

    public function useCase(): UseCaseKernel
    {
        return $this->useCaseKernel;
    }

    public function controller(): ControllerKernel
    {
        return $this->controllerKernel;
    }

    public function router(): RouterKernel
    {
        return $this->routerKernel;
    }
}
