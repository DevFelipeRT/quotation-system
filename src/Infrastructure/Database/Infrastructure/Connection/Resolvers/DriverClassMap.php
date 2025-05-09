<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Connection\Resolvers;

use App\Infrastructure\Database\Infrastructure\Connection\Drivers\MySqlConnection;
use App\Infrastructure\Database\Infrastructure\Connection\Drivers\PostgreSqlConnection;
use App\Infrastructure\Database\Infrastructure\Connection\Drivers\SqliteConnection;

/**
 * Provides the mapping between supported driver identifiers and their connection implementations.
 *
 * This class acts as a centralized registry of known drivers and their associated connection classes.
 * It supports Open/Closed compliance by isolating configuration from resolution logic.
 *
 * @package App\Infrastructure\Database\Connection\Resolvers
 */
final class DriverClassMap
{
    /**
     * Returns a map of driver identifiers to connection class names.
     *
     * @return array<string, class-string<DatabaseConnectionInterface>>
     */
    public static function get(): array
    {
        return [
            'mysql'  => MySqlConnection::class,
            'pgsql'  => PostgreSqlConnection::class,
            'sqlite' => SqliteConnection::class,
        ];
    }
}
