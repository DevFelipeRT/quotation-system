<?php

declare(strict_types=1);

namespace Database\Application\Execution;

use Database\Domain\Connection\DatabaseConnectionInterface;
use Database\Domain\Execution\DatabaseRequestInterface;
use Database\Domain\Execution\Resolvers\RequestBuilderResolverInterface;
use App\Shared\Event\Contracts\EventDispatcherInterface;

/**
 * Factory for creating database request executors.
 *
 * Delegates construction to a resolved RequestBuilderInterface instance,
 * based on the provided DatabaseConnectionInterface.
 */
final class RequestFactory
{
    public function __construct(
        private readonly RequestBuilderResolverInterface $resolver,
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    /**
     * Creates a request executor based on the resolved builder and dispatcher.
     *
     * @param DatabaseConnectionInterface $connection
     * @return DatabaseRequestInterface
     */
    public function createFromConnection(DatabaseConnectionInterface $connection): DatabaseRequestInterface
    {
        $pdo = $connection->connect();
        $builder = $this->resolver->resolve($connection);

        return $builder->build($pdo, $this->dispatcher);
    }
}
