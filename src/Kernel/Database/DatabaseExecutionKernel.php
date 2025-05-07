<?php

declare(strict_types=1);

namespace App\Kernel\Database;

use App\Infrastructure\Database\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Execution\DatabaseRequestInterface;
use App\Infrastructure\Database\Execution\RequestFactory;
use App\Infrastructure\Database\Execution\Observers\QueryLoggerObserver;
use App\Infrastructure\Database\Execution\Resolvers\RequestBuilderResolver;
use App\Infrastructure\Database\Observers\RequestObserverInterface;
use App\Logging\LoggerInterface;
use Throwable;

/**
 * Class DatabaseExecutionKernel
 *
 * Coordinates the initialization of the database query execution layer.
 *
 * This kernel is responsible for:
 * - Constructing the DatabaseRequestInterface from an active connection.
 * - Registering execution observers such as loggers.
 * - Handling errors in a controlled way.
 *
 * @package App\Kernel\Database
 */
final class DatabaseExecutionKernel
{
    private readonly DatabaseRequestInterface $request;
    private readonly array $observers;
    private readonly RequestBuilderResolver $resolver;
    private readonly RequestFactory $factory;

    public function __construct(DatabaseConnectionInterface $connection, LoggerInterface $logger)
    {
        $this->observers = $this->defaultObservers($logger);
        $this->resolver = $this->resolveBuilder();
        $this->factory = $this->createFactory();
        $this->request = $this->safelyCreateRequest($connection);
    }

    /**
     * Returns the ready-to-use query request interface.
     *
     * @return DatabaseRequestInterface
     */
    public function request(): DatabaseRequestInterface
    {
        return $this->request;
    }

    /**
     * Resolves the request builder resolver.
     *
     * @return RequestBuilderResolver
     */
    private function resolveBuilder(): RequestBuilderResolver
    {
        return new RequestBuilderResolver();
    }

    /**
     * Creates the factory using the internal resolver.
     *
     * @return RequestFactory
     */
    private function createFactory(): RequestFactory
    {
        return new RequestFactory($this->resolver);
    }

    /**
     * Attempts to create the request and handles any failure.
     *
     * @param DatabaseConnectionInterface $connection
     * @return DatabaseRequestInterface
     */
    private function safelyCreateRequest(DatabaseConnectionInterface $connection): DatabaseRequestInterface
    {
        try {
            return $this->factory->createFromConnection($connection, $this->observers);
        } catch (Throwable $e) {
            throw new \RuntimeException('Failed to initialize database request layer.', 0, $e);
        }
    }

    /**
     * Defines the default observers for query instrumentation.
     *
     * @param LoggerInterface $logger
     * @return RequestObserverInterface[]
     */
    private function defaultObservers(LoggerInterface $logger): array
    {
        return [new QueryLoggerObserver($logger)];
    }
}
