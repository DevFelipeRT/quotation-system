<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Connection\Resolvers;

use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Domain\Connection\Resolvers\DriverResolverInterface;
use App\Infrastructure\Database\Exceptions\UnsupportedDriverException;
use Config\Database\SupportedDrivers;

/**
 * Resolves the appropriate connection class for a given driver identifier.
 *
 * Combines validated driver resolution from SupportedDrivers with
 * the static implementation map defined in DriverClassMap.
 *
 * @package App\Infrastructure\Database\Connection\Resolvers
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
        $normalized = SupportedDrivers::resolve($driver);
        SupportedDrivers::assertIsSupported($normalized);

        $map = DriverClassMap::get();

        if (!isset($map[$normalized])) {
            throw new UnsupportedDriverException($normalized);
        }

        return $map[$normalized];
    }
}