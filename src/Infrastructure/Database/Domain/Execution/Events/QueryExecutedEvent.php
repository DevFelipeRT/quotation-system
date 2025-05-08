<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Domain\Execution\Events;

/**
 * Event dispatched after a successful SQL execution.
 *
 * Provides details for instrumentation layers (e.g. logging, metrics),
 * without exposing sensitive database internals.
 */
final class QueryExecutedEvent
{
    /**
     * @param string $query        The SQL query executed.
     * @param array  $parameters   The bound parameters used in the execution.
     * @param int    $affectedRows Number of rows affected or returned.
     */
    public function __construct(
        public readonly string $query,
        public readonly array $parameters,
        public readonly int $affectedRows
    ) {}
}
