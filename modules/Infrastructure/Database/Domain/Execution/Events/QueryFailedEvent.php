<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Domain\Execution\Events;

/**
 * Event dispatched when a SQL query execution fails.
 *
 * Provides structured context for logging, debugging or alerting subsystems.
 * Designed to avoid direct exposure of raw exception objects or internal stack traces.
 */
final class QueryFailedEvent
{
    /**
     * @param string $query         The SQL query that failed.
     * @param array  $parameters    Parameters passed to the failed query.
     * @param string $errorMessage  A safe, high-level error description.
     */
    public function __construct(
        public readonly string $query,
        public readonly array $parameters,
        public readonly string $errorMessage
    ) {}
}
