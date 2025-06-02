<?php

declare(strict_types=1);

namespace Database\Domain\Execution\Resolvers;

use Database\Domain\Connection\DatabaseConnectionInterface;
use Database\Domain\Execution\RequestBuilders\RequestBuilderInterface;

/**
 * Contract for resolving a request builder based on the connection type.
 *
 * Implementations are responsible for instantiating and configuring a compatible
 * RequestBuilderInterface instance that supports the runtime connection.
 *
 * Builders may require additional collaborators (e.g., dispatcher).
 */
interface RequestBuilderResolverInterface
{
    /**
     * Resolves the appropriate builder for the given connection.
     *
     * @param DatabaseConnectionInterface $connection The active database connection instance.
     * @return RequestBuilderInterface A compatible builder instance.
     *
     * @throws \RuntimeException If no builder is available for the connection type.
     */
    public function resolve(DatabaseConnectionInterface $connection): RequestBuilderInterface;
}
