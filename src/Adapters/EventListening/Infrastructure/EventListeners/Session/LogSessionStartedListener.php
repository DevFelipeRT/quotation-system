<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\EventListeners\Session;

use App\Adapters\EventListening\Domain\Support\AbstractEventListener;
use App\Infrastructure\Session\Domain\Events\SessionStartedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs the moment a session is started with its initial state.
 *
 * @extends AbstractEventListener<SessionStartedEvent>
 */
final class LogSessionStartedListener extends AbstractEventListener
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    /**
     * Declares the event type this listener handles.
     *
     * @return class-string<SessionStartedEvent>
     */
    protected function eventType(): string
    {
        return SessionStartedEvent::class;
    }

    /**
     * Handles the event when a session is started.
     *
     * @param SessionStartedEvent $event
     * @return void
     */
    protected function handle(object $event): void
    {
        $this->logger->info('Session started', [
            'session_id' => $event->getSessionId(),
            'locale' => $event->getData()->getContext()->getLocale(),
            'authenticated' => $event->getData()->getContext()->isAuthenticated(),
        ]);
    }
}
