<?php

declare(strict_types=1);

namespace Database\Domain\Connection\Resolvers;

use Database\Domain\Connection\DatabaseConnectionInterface;

/**
 * Defines a contract for resolving the database connection class
 * based on a driver identifier (e.g. "mysql", "pgsql", "sqlite").
 *
 * This interface decouples the logic of driver-to-class resolution
 * from factories and consumers, enabling flexible and testable mappings.
 */
interface DriverResolverInterface
{
    /**
     * Returns the fully qualified class name responsible for handling the given driver.
     *
     * @param string $driver The configured database driver name.
     * @return class-string<DatabaseConnectionInterface> The concrete connection class to be instantiated.
     *
     * @throws UnsupportedDriverException If the given driver is not supported or mapped.
     */
    public function resolve(string $driver): string;
}
