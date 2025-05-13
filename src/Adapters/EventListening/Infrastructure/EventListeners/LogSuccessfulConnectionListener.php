<?php

namespace App\Adapters\EventListening\Infrastructure\EventListeners;

use App\Adapters\EventListening\Domain\Contracts\EventListenerInterface;
use App\Infrastructure\Database\Domain\Connection\Events\ConnectionSucceededEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs when a database connection is successfully established.
 */
final class LogSuccessfulConnectionListener implements EventListenerInterface
{
    public function __construct(private readonly PsrLoggerInterface $logger) {}

    /**
     * Invoked when a ConnectionSucceededEvent is dispatched.
     *
     * @param object $event
     * @return void
     */
    public function __invoke(object $event): void
    {
        if (!$event instanceof ConnectionSucceededEvent) {
            return;
        }

        $this->logger->info('Database connection succeeded');
    }
}
