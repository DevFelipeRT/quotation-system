<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\EventListeners;

use App\Adapters\EventListening\Domain\Support\AbstractEventListener;
use App\Infrastructure\Database\Domain\Connection\Events\ConnectionFailedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs information about failed database connection attempts.
 */
final class LogConnectionFailureListener extends AbstractEventListener
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    protected function eventType(): string
    {
        return ConnectionFailedEvent::class;
    }

    protected function handle(object $event): void
    {
        /** @var ConnectionFailedEvent $event */
        $this->logger->error(sprintf(
            '[Database] Connection failed using driver "%s": %s',
            $event->driver,
            $event->error
        ), $event->metadata);
    }

    protected function onFailure(object $event, \Throwable $exception): void
    {
        // Opcional: logar falha no próprio listener (ex: logger indisponível)
    }
}
