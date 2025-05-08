<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Execution\Resolvers;

use RuntimeException;

/**
 * Resolves the appropriate request builder implementation
 * for a given database connection.
 *
 * Synchronizes the execution strategy with the underlying connection type.
 */
final class RequestBuilderResolver implements RequestBuilderResolverInterface
{
    /**
     * Resolves the request builder for the given connection instance.
     *
     * @param DatabaseConnectionInterface $connection
     * @return RequestBuilderInterface
     *
     * @throws RuntimeException If the connection type is unsupported.
     */
    public function resolve(DatabaseConnectionInterface $connection): RequestBuilderInterface
    {
        if ($connection instanceof AbstractPdoConnection) {
            return new PdoRequestBuilder();
        }

        throw new RuntimeException(
            sprintf('No compatible RequestBuilder found for connection type: %s', get_class($connection))
        );
    }
}