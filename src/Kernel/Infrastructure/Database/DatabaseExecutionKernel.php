<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure\Database;

use App\Infrastructure\Database\Application\Execution\RequestFactory;
use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Domain\Execution\DatabaseRequestInterface;
use App\Infrastructure\Database\Infrastructure\Execution\Resolvers\RequestBuilderResolver;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use RuntimeException;
use Throwable;

/**
 * Initializes the database query execution layer.
 *
 * This kernel constructs a fully configured DatabaseRequestInterface using a factory.
 * The query lifecycle is instrumented via emitted domain events.
 */
final class DatabaseExecutionKernel
{
    private readonly DatabaseRequestInterface $request;

    public function __construct(
        DatabaseConnectionInterface $connection,
        EventDispatcherInterface $dispatcher
    ) {
        $resolver = new RequestBuilderResolver($dispatcher);
        $factory = new RequestFactory($resolver, $dispatcher);

        $this->request = $this->createRequest($factory, $connection);
    }

    /**
     * Returns the request interface for executing queries.
     *
     * @return DatabaseRequestInterface
     */
    public function request(): DatabaseRequestInterface
    {
        return $this->request;
    }

    /**
     * Handles request creation and captures any factory failures.
     *
     * @param RequestFactory $factory
     * @param DatabaseConnectionInterface $connection
     * @return DatabaseRequestInterface
     */
    private function createRequest(
        RequestFactory $factory,
        DatabaseConnectionInterface $connection
    ): DatabaseRequestInterface {
        try {
            return $factory->createFromConnection($connection);
        } catch (Throwable $e) {
            throw new RuntimeException('Failed to initialize the database request layer.', 0, $e);
        }
    }
}
