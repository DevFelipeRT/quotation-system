<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Execution\Resolvers;

use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Domain\Execution\RequestBuilders\RequestBuilderInterface;
use App\Infrastructure\Database\Domain\Execution\Resolvers\RequestBuilderResolverInterface;
use App\Infrastructure\Database\Infrastructure\Connection\AbstractPdoConnection;
use App\Infrastructure\Database\Infrastructure\Execution\RequestBuilders\PdoRequestBuilder;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use RuntimeException;

/**
 * Resolves the appropriate request builder implementation
 * for a given database connection.
 *
 * All returned builders are preconfigured with required collaborators.
 */
final class RequestBuilderResolver implements RequestBuilderResolverInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    public function resolve(DatabaseConnectionInterface $connection): RequestBuilderInterface
    {
        if ($connection instanceof AbstractPdoConnection) {
            return new PdoRequestBuilder($this->dispatcher);
        }

        throw new RuntimeException(
            sprintf('No compatible RequestBuilder found for connection type: %s', get_class($connection))
        );
    }
}
