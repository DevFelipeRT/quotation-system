<?php

declare(strict_types=1);

namespace Database\Infrastructure\Execution\Resolvers;

use Database\Domain\Connection\DatabaseConnectionInterface;
use Database\Domain\Execution\RequestBuilders\RequestBuilderInterface;
use Database\Domain\Execution\Resolvers\RequestBuilderResolverInterface;
use Database\Infrastructure\Connection\AbstractPdoConnection;
use Database\Infrastructure\Execution\RequestBuilders\PdoRequestBuilder;
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
