<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Execution;

use App\Infrastructure\Database\Execution\DatabaseRequestInterface;
use App\Infrastructure\Database\Execution\PdoDatabaseRequest;
use App\Infrastructure\Database\Observers\RequestObserverInterface;
use PDO;

/**
 * Factory for creating database request executors.
 *
 * Encapsulates the instantiation of DatabaseRequestInterface implementations
 * while supporting observer injection for query instrumentation (e.g. logging, metrics).
 */
final class RequestFactory
{
    /**
     * @param PDO $pdo Active PDO database connection.
     * @param RequestObserverInterface[] $observers Optional list of query observers.
     */
    public function __construct(
        private readonly PDO $pdo,
        private readonly array $observers = []
    ) {}

    /**
     * Creates a fully configured SQL request executor.
     *
     * @return DatabaseRequestInterface
     */
    public function create(): DatabaseRequestInterface
    {
        return new PdoDatabaseRequest($this->pdo, $this->observers);
    }
}
