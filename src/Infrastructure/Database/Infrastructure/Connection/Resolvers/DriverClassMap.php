<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Connection\Resolvers;

use App\Infrastructure\Database\Infrastructure\Connection\Drivers\MySqlConnection;
use App\Infrastructure\Database\Infrastructure\Connection\Drivers\PostgreSqlConnection;
use App\Infrastructure\Database\Infrastructure\Connection\Drivers\SqliteConnection;
use Config\Database\SupportedDrivers;
use InvalidArgumentException;

/**
 * Provides the mapping between supported driver identifiers and their connection implementations.
 *
 * This class acts as a centralized registry of known drivers and their associated connection classes.
 * It validates against SupportedDrivers to ensure consistency and enforce correctness.
 */
final class DriverClassMap
{
    /**
     * Returns a map of driver identifiers to connection class names.
     *
     * @return array<string, class-string>
     */
    public static function get(): array
    {
        $map = [
            'mysql'  => MySqlConnection::class,
            'pgsql'  => PostgreSqlConnection::class,
            'sqlite' => SqliteConnection::class,
        ];

        self::validateAgainstSupportedDrivers(array_keys($map));

        return $map;
    }

    /**
     * Ensures all registered drivers are declared as supported.
     *
     * @param string[] $drivers
     * @return void
     * @throws InvalidArgumentException
     */
    private static function validateAgainstSupportedDrivers(array $drivers): void
    {
        $supported = SupportedDrivers::list();
        foreach ($drivers as $driver) {
            if (!in_array($driver, $supported, true)) {
                throw new InvalidArgumentException("Driver '{$driver}' in DriverClassMap is not supported by SupportedDrivers.");
            }
        }
    }
}
