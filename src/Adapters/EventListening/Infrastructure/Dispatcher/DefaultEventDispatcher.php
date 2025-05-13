<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\Dispatcher;

use App\Shared\Event\Contracts\EventDispatcherInterface;
use App\Shared\Event\Contracts\EventListenerLocatorInterface;

/**
 * Dispatches events to all dynamically resolved listeners.
 *
 * This dispatcher relies on a locator strategy to resolve the
 * appropriate listeners for a given event at runtime.
 */
final class DefaultEventDispatcher implements EventDispatcherInterface
{
    private EventListenerLocatorInterface $locator;

    /**
     * @param EventListenerLocatorInterface $locator
     *        Strategy for resolving applicable listeners per event.
     */
    public function __construct(EventListenerLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Dispatches the event to all resolved listeners.
     *
     * @param object $event
     * @return void
     */
    public function dispatch(object $event): void
    {
        foreach ($this->locator->listenersFor($event) as $listener) {
            $listener($event);
        }
    }
}
