<?php

declare(strict_types=1);

namespace App\Kernel\Container;

use App\Infrastructure\Logging\Infrastructure\Contracts\LoggerInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;
use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Shared\Event\Contracts\EventDispatcherInterface;

/**
 * ServiceBindings
 *
 * Centralizes all dependency bindings for the application's service container.
 * SRP: Each method in this class is responsible for a single, well-defined task.
 *
 * @package App\Kernel\Container
 */
final class ServiceBindings
{
    /**
     * Provides the minimal binding set required for technical bootstrap.
     * Only logger and psrLogger are available at this phase.
     *
     * @param LoggerInterface $logger
     * @param PsrLoggerInterface $psrLogger
     * @return array<string, object>
     */
    public static function bootstrap(
        LoggerInterface $logger,
        PsrLoggerInterface $psrLogger
    ): array {
        self::assertLogger($logger);
        self::assertPsrLogger($psrLogger);

        return self::mapBootstrap($logger, $psrLogger);
    }

    /**
     * Provides the full binding map after all technical dependencies are available.
     *
     * @param LoggerInterface $logger
     * @param DatabaseConnectionInterface $dbConnection
     * @param EventDispatcherInterface $eventDispatcher
     * @param PsrLoggerInterface $psrLogger
     * @return array<string, object>
     */
    public static function get(
        LoggerInterface $logger,
        DatabaseConnectionInterface $dbConnection,
        EventDispatcherInterface $eventDispatcher,
        PsrLoggerInterface $psrLogger
    ): array {
        self::assertLogger($logger);
        self::assertDatabaseConnection($dbConnection);
        self::assertEventDispatcher($eventDispatcher);
        self::assertPsrLogger($psrLogger);

        return self::mapFinal(
            $logger,
            $dbConnection,
            $eventDispatcher,
            $psrLogger
        );
    }

    // ===== Binding mappers =====

    /**
     * Returns the bootstrap bindings map.
     *
     * @param LoggerInterface $logger
     * @param PsrLoggerInterface $psrLogger
     * @return array<string, object>
     */
    private static function mapBootstrap(
        LoggerInterface $logger,
        PsrLoggerInterface $psrLogger
    ): array {
        return [
            LoggerInterface::class    => $logger,
            PsrLoggerInterface::class => $psrLogger,
        ];
    }

    /**
     * Returns the final, full bindings map.
     *
     * @param LoggerInterface $logger
     * @param DatabaseConnectionInterface $dbConnection
     * @param EventDispatcherInterface $eventDispatcher
     * @param PsrLoggerInterface $psrLogger
     * @return array<string, object>
     */
    private static function mapFinal(
        LoggerInterface $logger,
        DatabaseConnectionInterface $dbConnection,
        EventDispatcherInterface $eventDispatcher,
        PsrLoggerInterface $psrLogger
    ): array {
        return [
            LoggerInterface::class                => $logger,
            PsrLoggerInterface::class             => $psrLogger,
            DatabaseConnectionInterface::class    => $dbConnection,
            EventDispatcherInterface::class       => $eventDispatcher,
        ];
    }

    // ===== Validators (SRP: One per contract) =====

    /**
     * Validates that LoggerInterface is not null.
     *
     * @param LoggerInterface|null $logger
     * @throws \InvalidArgumentException
     */
    private static function assertLogger(?LoggerInterface $logger): void
    {
        if (!$logger) {
            throw new \InvalidArgumentException('LoggerInterface instance must not be null.');
        }
    }

    /**
     * Validates that DatabaseConnectionInterface is not null.
     *
     * @param DatabaseConnectionInterface|null $dbConnection
     * @throws \InvalidArgumentException
     */
    private static function assertDatabaseConnection(?DatabaseConnectionInterface $dbConnection): void
    {
        if (!$dbConnection) {
            throw new \InvalidArgumentException('DatabaseConnectionInterface instance must not be null.');
        }
    }

    /**
     * Validates that EventDispatcherInterface is not null.
     *
     * @param EventDispatcherInterface|null $eventDispatcher
     * @throws \InvalidArgumentException
     */
    private static function assertEventDispatcher(?EventDispatcherInterface $eventDispatcher): void
    {
        if (!$eventDispatcher) {
            throw new \InvalidArgumentException('EventDispatcherInterface instance must not be null.');
        }
    }

    /**
     * Validates that PsrLoggerInterface is not null.
     *
     * @param PsrLoggerInterface|null $psrLogger
     * @throws \InvalidArgumentException
     */
    private static function assertPsrLogger(?PsrLoggerInterface $psrLogger): void
    {
        if (!$psrLogger) {
            throw new \InvalidArgumentException('PsrLoggerInterface instance must not be null.');
        }
    }
}
