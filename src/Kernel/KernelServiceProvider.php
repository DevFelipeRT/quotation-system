<?php

declare(strict_types=1);

namespace App\Kernel;

use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\LoggerInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;
use App\Shared\Container\Domain\Contracts\ContainerInterface;
use App\Shared\Container\Domain\Contracts\ServiceProviderInterface;
use App\Shared\Event\Contracts\EventDispatcherInterface;

/**
 * ServiceProvider responsible for registering essential bindings and external instances
 * into the application's dependency container.
 *
 * Follows the Service Provider pattern similar to Laravel, enabling modular, testable,
 * and expressive binding logic. This provider should be instantiated and registered
 * in the application's kernel or bootstrapper.
 */
class KernelServiceProvider implements ServiceProviderInterface
{
    /** @var ContainerInterface */
    private ContainerInterface $container;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /** @var PsrLoggerInterface */
    private PsrLoggerInterface $psrLogger;

    /** @var DatabaseConnectionInterface|null */
    private ?DatabaseConnectionInterface $dbConnection = null;

    /** @var EventDispatcherInterface|null */
    private ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @param LoggerInterface $logger
     * @param PsrLoggerInterface $psrLogger
     */
    public function __construct(
        LoggerInterface $logger,
        PsrLoggerInterface $psrLogger
    ) {
        $this->logger = $logger;
        $this->psrLogger = $psrLogger;
    }

    /**
     * Register essential bindings and instances into the provided container.
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function register(ContainerInterface $container): void
    {
        $this->container = $container;
        $this->container->singleton(LoggerInterface::class, fn () => $this->logger);
        $this->container->singleton(PsrLoggerInterface::class, fn () => $this->psrLogger);
    }

    /**
     * Register Infrastructure bindings and instances into the provided container.
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function registerInfrastructure(
        ?DatabaseConnectionInterface $dbConnection = null, 
        ?EventDispatcherInterface $eventDispatcher = null
    ): void
    {
        if ($dbConnection !== null) {
            $this->container->singleton(DatabaseConnectionInterface::class, fn () => $this->dbConnection);
        }
        if ($eventDispatcher !== null) {
            $this->container->singleton(EventDispatcherInterface::class, fn () => $this->eventDispatcher);
        }
    }

    /**
     * Bootstraps any logic that requires the container to be fully configured.
     *
     * This method is invoked after all service providers have completed their registration.
     * Typical use cases include registering event listeners, extending services, or executing
     * integration hooks that rely on other services already being available in the container.
     *
     * @param ContainerInterface $container The fully built dependency container.
     * @return void
     */
    public function boot(ContainerInterface $container): void
    {
        
    }

}
