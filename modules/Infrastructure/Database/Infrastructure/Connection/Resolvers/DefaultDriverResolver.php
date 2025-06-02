<?php

declare(strict_types=1);

namespace Database\Infrastructure\Connection\Resolvers;

use Database\Domain\Connection\DatabaseConnectionInterface;
use Database\Domain\Connection\Resolvers\DriverResolverInterface;
use Database\Exceptions\UnsupportedDriverException;
use Database\Infrastructure\Connection\DriverClassMap;
use Database\Validation\DriverValidator;


/**
 * Resolves the appropriate connection class for a given driver identifier.
 *
 * Combines validated driver resolution from DriverValidator with
 * the static implementation map defined in DriverClassMap.
 */
final class DefaultDriverResolver implements DriverResolverInterface
{
    /**
     * Resolves the class name for the connection associated with the given driver.
     *
     * @param string $driver
     * @return class-string<DatabaseConnectionInterface>
     *
     * @throws UnsupportedDriverException
     */
    public function resolve(string $driver): string
    {
        $normalized = DriverValidator::resolve($driver);
        DriverValidator::assertIsSupported($normalized);

        $map = DriverClassMap::get();

        if (!isset($map[$normalized])) {
            throw new UnsupportedDriverException($normalized);
        }

        return $map[$normalized];
    }
}
