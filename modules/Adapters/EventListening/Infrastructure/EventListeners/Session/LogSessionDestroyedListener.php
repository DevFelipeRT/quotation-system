<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\EventListeners\Session;

use App\Adapters\EventListening\Domain\Support\AbstractEventListener;
use App\Infrastructure\Session\Domain\Events\SessionDestroyedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs when a session is destroyed, including its final state.
 *
 * @extends AbstractEventListener<SessionDestroyedEvent>
 */
final class LogSessionDestroyedListener extends AbstractEventListener
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    /**
     * Declares the event type this listener handles.
     *
     * @return class-string<SessionDestroyedEvent>
     */
    protected function eventType(): string
    {
        return SessionDestroyedEvent::class;
    }

    /**
     * Handles the event when a session is destroyed.
     *
     * @param SessionDestroyedEvent $event
     * @return void
     */
    protected function handle(object $event): void
    {
        $this->logger->info('Session destroyed', [
            'destroyed_at' => $event->occurredAt()->format(DATE_ATOM),
            'final_state' => $event->getPreviousData()->toArray(),
        ]);
    }
}
