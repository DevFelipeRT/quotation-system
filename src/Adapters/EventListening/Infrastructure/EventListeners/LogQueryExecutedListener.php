<?php

namespace App\Adapters\EventListening\Infrastructure\EventListeners;

use App\Adapters\EventListening\Domain\Contracts\EventListenerInterface;
use App\Infrastructure\Database\Domain\Execution\Events\QueryExecutedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs metadata after a query is successfully executed.
 */
final class LogQueryExecutedListener implements EventListenerInterface
{
    public function __construct(private readonly PsrLoggerInterface $logger) {}

    /**
     * Invoked when a QueryExecutedEvent is dispatched.
     *
     * @param object $event
     * @return void
     */
    public function __invoke(object $event): void
    {
        if (!$event instanceof QueryExecutedEvent) {
            return;
        }

        $this->logger->info('Database query executed', [
            'query' => $event->query,
            'parameters' => $event->parameters,
            'affected_rows' => $event->affectedRows,
        ]);
    }
}
