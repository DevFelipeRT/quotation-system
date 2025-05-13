<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Application\Resolver;

use App\Adapters\EventListening\Domain\Contracts\EventListenerInterface;
use App\Shared\Event\Contracts\EventListenerLocatorInterface;

/**
 * Resolves all event listeners associated with a given event instance.
 *
 * Acts as a runtime adapter to EventListenerMap, exposing listener resolution
 * behavior for use by dispatcher components.
 */
final class EventListenerResolver implements EventListenerLocatorInterface
{
    public function __construct(
        private readonly EventListenerMap $map
    ) {}

    /**
     * Returns all listeners registered for the given event.
     *
     * @param object $event
     * @return iterable<EventListenerInterface>
     */
    public function listenersFor(object $event): iterable
    {
        return $this->map->for($event);
    }

    /**
     * Returns the complete internal event-to-listeners map.
     * Intended for introspection, diagnostics or testing.
     *
     * @return array<class-string, EventListenerInterface[]>
     */
    public function all(): array
    {
        return $this->map->all();
    }
}
