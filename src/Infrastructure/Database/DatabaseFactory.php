<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Infrastructure\Database\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Connection\PdoConnection;
use App\Infrastructure\Database\Connection\ConnectionObserverInterface;
use App\Infrastructure\Database\Exceptions\UnsupportedDriverException;
use Config\Database\DatabaseConfig;

/**
 * Factory responsible for instantiating the correct database connection strategy
 * based on runtime configuration. It supports injection of observers for monitoring.
 */
final class DatabaseFactory
{
    /**
     * Creates a database connection implementation based on the configured driver.
     *
     * @param DatabaseConfig $config    Structured configuration for database access.
     * @param ConnectionObserverInterface[] $observers Optional list of observers for connection events.
     * @return DatabaseConnectionInterface
     *
     * @throws UnsupportedDriverException If the configured driver is not recognized.
     */
    public static function make(
        DatabaseConfig $config,
        array $observers = []
    ): DatabaseConnectionInterface {
        return match ($config->driver()) {
            'mysql', 'pgsql', 'sqlite' => new PdoConnection($config, $observers),
            default => throw new UnsupportedDriverException($config->driver())
        };
    }
}
