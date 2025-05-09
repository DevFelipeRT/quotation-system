<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Domain\Execution\Resolvers;

use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Domain\Execution\RequestBuilders\RequestBuilderInterface;

/**
 * Contract for resolving a RequestBuilderInterface based on a given database connection.
 */
interface RequestBuilderResolverInterface
{
    /**
     * Resolves the appropriate request builder for the provided connection.
     *
     * @param DatabaseConnectionInterface $connection
     * @return RequestBuilderInterface
     */
    public function resolve(DatabaseConnectionInterface $connection): RequestBuilderInterface;
}
