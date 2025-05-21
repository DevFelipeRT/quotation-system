<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\EventListening\Providers;

use App\Kernel\Adapters\EventListening\Contracts\EventBindingProviderInterface;
use App\Adapters\EventListening\Infrastructure\EventListeners\Session\{
    LogSessionStartedListener,
    LogSessionDataChangedListener,
    LogSessionDestroyedListener
};
use App\Infrastructure\Session\Domain\Events\{
    SessionStartedEvent,
    SessionDataChangedEvent,
    SessionDestroyedEvent
};

/**
 * SessionEventBindingProvider
 *
 * Declares bindings between session-related events and their listener classes.
 * Does not instantiate listeners or require dependencies; all listeners are resolved dynamically.
 */
final class SessionEventBindingProvider implements EventBindingProviderInterface
{
    /**
     * Returns event-to-listener class bindings for session domain events.
     *
     * @return array<class-string, class-string[]>
     */
    public function bindings(): array
    {
        return [
            SessionStartedEvent::class => [
                LogSessionStartedListener::class,
            ],
            SessionDataChangedEvent::class => [
                LogSessionDataChangedListener::class,
            ],
            SessionDestroyedEvent::class => [
                LogSessionDestroyedListener::class,
            ],
        ];
    }
}
