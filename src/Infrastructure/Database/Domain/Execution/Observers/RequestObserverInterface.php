<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Domain\Execution\Observers;

/**
 * Defines a contract for observing database query execution events.
 *
 * Implementations may respond to successful executions, failures, or other query lifecycle transitions.
 * Typical observers include loggers, metrics collectors, auditing tools, etc.
 */
interface RequestObserverInterface
{
    /**
     * Handles a dispatched query event.
     *
     * @param object $event The emitted event (e.g. QueryExecutedEvent, QueryFailedEvent).
     * @return void
     */
    public function handle(object $event): void;
}
