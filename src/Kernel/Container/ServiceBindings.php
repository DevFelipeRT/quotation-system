<?php

declare(strict_types=1);

namespace App\Kernel\Container;

use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\LoggerInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;
use App\Shared\Container\AppContainerInterface;
use App\Shared\Event\Contracts\EventDispatcherInterface;

/**
 * ServiceBindings
 *
 * Centralizes all dependency bindings for the application's service container.
 * After the introduction of autowiring, this class binds only abstract contracts or external instances.
 */
final class ServiceBindings
{
    /**
     * Registers the minimal bindings required for bootstrapping the system.
     * These are typically not resolvable via autowiring alone.
     *
     * @param AppContainerInterface $container
     * @param LoggerInterface $logger
     * @param PsrLoggerInterface $psrLogger
     * @return void
     */
    public static function bootstrap(
        AppContainerInterface $container,
        LoggerInterface $logger,
        PsrLoggerInterface $psrLogger
    ): void {
        $container->singleton(LoggerInterface::class, fn () => $logger);
        $container->singleton(PsrLoggerInterface::class, fn () => $psrLogger);
    }

    /**
     * Registers the remaining bindings after full initialization.
     * Only interfaces or pre-built objects need to be bound explicitly.
     *
     * @param AppContainerInterface $container
     * @param DatabaseConnectionInterface $dbConnection
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public static function register(
        AppContainerInterface $container,
        DatabaseConnectionInterface $dbConnection,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $container->singleton(DatabaseConnectionInterface::class, fn () => $dbConnection);
        $container->singleton(EventDispatcherInterface::class, fn () => $eventDispatcher);
    }
}
