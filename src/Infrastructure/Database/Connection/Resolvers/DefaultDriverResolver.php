<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Connection\Resolvers;

use App\Infrastructure\Database\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Connection\PdoConnection;
use App\Infrastructure\Database\Exceptions\UnsupportedDriverException;

/**
 * Resolves a concrete database connection class based on a given driver name.
 *
 * This implementation provides a static mapping between known driver identifiers
 * and their corresponding connection classes. It is intended to be injected
 * into factories that need to delegate driver-specific instantiation.
 */
final class DefaultDriverResolver implements DriverResolverInterface
{
    /**
     * Maps supported drivers to their corresponding connection class names.
     *
     * @var array<string, class-string<DatabaseConnectionInterface>>
     */
    private const DRIVER_MAP = [
        'mysql'  => PdoConnection::class,
        'pgsql'  => PdoConnection::class,
        'sqlite' => PdoConnection::class,
    ];

    /**
     * Resolves the connection class name for a given driver.
     *
     * @param string $driver The configured driver identifier.
     * @return class-string<DatabaseConnectionInterface>
     *
     * @throws UnsupportedDriverException If no matching class is defined for the driver.
     */
    public function resolve(string $driver): string
    {
        if (!isset(self::DRIVER_MAP[$driver])) {
            throw new UnsupportedDriverException($driver);
        }

        return self::DRIVER_MAP[$driver];
    }
}
