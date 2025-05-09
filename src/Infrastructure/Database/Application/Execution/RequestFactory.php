<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Application\Execution;

use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Domain\Execution\DatabaseRequestInterface;
use App\Infrastructure\Database\Domain\Execution\Resolvers\RequestBuilderResolverInterface;
use PDO;

/**
 * Factory for creating database request executors.
 *
 * Delegates request creation to a resolved RequestBuilderInterface implementation,
 * based on the provided DatabaseConnectionInterface instance.
 */
final class RequestFactory
{
    public function __construct(
        private readonly RequestBuilderResolverInterface $resolver
    ) {}

    /**
     * Creates a fully configured SQL request executor from a PDO connection.
     *
     * @param PDO $pdo
     * @param RequestObserverInterface[] $observers
     * @return DatabaseRequestInterface
     */
    public function create(PDO $pdo, array $observers = []): DatabaseRequestInterface
    {
        throw new \LogicException('Use createFromConnection instead when using a resolver-based factory.');
    }

    /**
     * Creates a request executor based on a resolved builder from the given connection.
     *
     * @param DatabaseConnectionInterface $connection
     * @param RequestObserverInterface[] $observers
     * @return DatabaseRequestInterface
     */
    public function createFromConnection(DatabaseConnectionInterface $connection, array $observers = []): DatabaseRequestInterface
    {
        $builder = $this->resolver->resolve($connection);
        return $builder->build($connection->connect(), $observers);
    }
}