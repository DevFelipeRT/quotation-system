<?php

namespace App\Infrastructure\Database;

use App\Infrastructure\Database\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Connection\PdoConnection;
use App\Interfaces\Infrastructure\LoggerInterface;
use Config\Container\ConfigContainer;
use RuntimeException;

/**
 * DatabaseFactory
 *
 * Factory responsible for selecting and instantiating the appropriate
 * database connection strategy at runtime based on environment configuration.
 *
 * Implements the Factory Method Pattern.
 */
final class DatabaseFactory
{
    /**
     * Selects and returns the appropriate database connection strategy.
     *
     * @param ConfigContainer   $config Application configuration container
     * @param LoggerInterface   $logger Logger for diagnostics
     * @return DatabaseConnectionInterface
     */
    public static function make(ConfigContainer $config, LoggerInterface $logger): DatabaseConnectionInterface
    {
        $driver = $config->database()->driver();

        return match ($driver) {
            'mysql', 'pgsql', 'sqlite' => new PdoConnection($config->database(), $logger),
            default => throw new RuntimeException("Unsupported DB driver: {$driver}")
        };
    }
}
