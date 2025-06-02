<?php

declare(strict_types=1);

namespace Database\Infrastructure\Execution\RequestBuilders;

use Database\Domain\Execution\DatabaseRequestInterface;
use Database\Domain\Execution\RequestBuilders\RequestBuilderInterface;
use Database\Infrastructure\Execution\PdoDatabaseRequest;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use PDO;

/**
 * Concrete builder for creating PDO-based request executors.
 *
 * This builder constructs a fully configured implementation of
 * DatabaseRequestInterface by wrapping a raw PDO instance with a
 * query-aware adapter that emits domain events for success/failure.
 *
 * The EventDispatcherInterface provided via constructor allows all
 * dispatched events (e.g., QueryExecutedEvent, QueryFailedEvent) to be
 * routed through the system’s event listening infrastructure.
 */
final class PdoRequestBuilder implements RequestBuilderInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    /**
     * Builds the request executor from the given PDO connection.
     *
     * @param PDO $pdo The active PDO instance.
     * @return DatabaseRequestInterface A fully instrumented request layer.
     */
    public function build(PDO $pdo): DatabaseRequestInterface
    {
        return new PdoDatabaseRequest($pdo, $this->dispatcher);
    }
}
