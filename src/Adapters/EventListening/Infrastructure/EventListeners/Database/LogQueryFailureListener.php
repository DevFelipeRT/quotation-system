<?php

namespace App\Adapters\EventListening\Infrastructure\EventListeners\Database;

use App\Adapters\EventListening\Domain\Contracts\EventListenerInterface;
use App\Infrastructure\Database\Domain\Execution\Events\QueryFailedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs details when a query fails during database execution.
 */
final class LogQueryFailureListener implements EventListenerInterface
{
    public function __construct(private readonly PsrLoggerInterface $logger) {}

    /**
     * Invoked when a QueryFailedEvent is dispatched.
     *
     * @param object $event
     * @return void
     */
    public function __invoke(object $event): void
    {
        if (!$event instanceof QueryFailedEvent) {
            return;
        }

        $this->logger->error('Database query failed', [
            'query' => $event->query,
            'parameters' => $event->parameters,
            'error_message' => $event->errorMessage,
        ]);
    }
}
