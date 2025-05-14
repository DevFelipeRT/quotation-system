<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Connection;

use App\Infrastructure\Database\Infrastructure\Connection\Drivers\{
    MySqlConnection,
    PostgreSqlConnection,
    SqliteConnection
};
use App\Infrastructure\Database\Validation\DriverValidator;
use InvalidArgumentException;

/**
 * Maps supported driver identifiers to their respective connection class implementations.
 *
 * Ensures that all keys used in the map are valid according to DriverValidator.
 */
final class DriverClassMap
{
    /**
     * @var array<string, class-string>
     */
    private const MAP = [
        'mysql'  => MySqlConnection::class,
        'pgsql'  => PostgreSqlConnection::class,
        'sqlite' => SqliteConnection::class,
    ];

    /**
     * Returns a validated map of supported drivers to connection classes.
     *
     * @return array<string, class-string>
     */
    public static function get(): array
    {
        self::validate();
        return self::MAP;
    }

    /**
     * Ensures all drivers declared in the map are recognized by the validator.
     *
     * @return void
     */
    private static function validate(): void
    {
        foreach (array_keys(self::MAP) as $driver) {
            if (!in_array($driver, DriverValidator::list(), true)) {
                throw new InvalidArgumentException(
                    "Driver '{$driver}' in DriverClassMap is not supported. Valid options: " . implode(', ', DriverValidator::list())
                );
            }
        }
    }
}
