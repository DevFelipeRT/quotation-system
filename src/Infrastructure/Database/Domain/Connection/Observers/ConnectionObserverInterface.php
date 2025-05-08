<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Domain\Connection\Observers;

/**
 * Represents an observer that listens to database connection lifecycle events.
 *
 * Implementations should handle specific event types such as connection success or failure,
 * and may perform side effects like logging, metrics collection, or notifications.
 */
interface ConnectionObserverInterface
{
    /**
     * Handles a dispatched connection event.
     *
     * @param object $event An event representing a connection state change.
     *                      Expected types include ConnectionSucceededEvent, ConnectionFailedEvent.
     * @return void
     */
    public function handle(object $event): void;
}
