<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\EventListeners\Session;

use App\Adapters\EventListening\Domain\Support\AbstractEventListener;
use App\Infrastructure\Session\Domain\Events\SessionDataChangedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs any change to the session data state.
 *
 * @extends AbstractEventListener<SessionDataChangedEvent>
 */
final class LogSessionDataChangedListener extends AbstractEventListener
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    /**
     * Declares the event type this listener handles.
     *
     * @return class-string<SessionDataChangedEvent>
     */
    protected function eventType(): string
    {
        return SessionDataChangedEvent::class;
    }

    /**
     * Handles the event when the session data changes.
     *
     * @param SessionDataChangedEvent $event
     * @return void
     */
    protected function handle(object $event): void
    {
        $this->logger->info('Session data changed', [
            'previous' => $event->getPreviousData()->toArray(),
            'updated' => $event->getNewData()->toArray(),
        ]);
    }
}
