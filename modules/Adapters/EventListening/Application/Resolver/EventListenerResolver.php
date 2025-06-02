<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Application\Resolver;

use App\Adapters\EventListening\Domain\Contracts\EventListenerInterface;
use App\Shared\Container\Domain\Contracts\ContainerInterface;
use App\Shared\Event\Contracts\EventListenerLocatorInterface;
use InvalidArgumentException;

/**
 * EventListenerResolver
 *
 * Resolves and instantiates all event listeners associated with a given event instance,
 * using the internal container for dependency injection.
 *
 * Acts as a runtime adapter to EventListenerMap, exposing listener resolution
 * for use by dispatcher components.
 */
final class EventListenerResolver implements EventListenerLocatorInterface
{
    private EventListenerMap $map;
    private ContainerInterface $container;

    public function __construct(
        EventListenerMap $map,
        ContainerInterface $container
    ) {
        $this->map = $map;
        $this->container = $container;
    }

    /**
     * Returns instantiated listeners for the given event, using the container.
     *
     * @param object $event
     * @return iterable<EventListenerInterface>
     */
    public function listenersFor(object $event): iterable
    {
        $listenerClasses = $this->map->for($event);
        foreach ($listenerClasses as $listenerClass) {
            $listener = $this->container->get($listenerClass);
            if (!$listener instanceof EventListenerInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Resolved listener for event "%s" is not an EventListenerInterface: %s',
                    get_class($event),
                    is_object($listener) ? get_class($listener) : gettype($listener)
                ));
            }
            yield $listener;
        }
    }

    /**
     * Returns the complete internal event-to-listener class map.
     * Intended for introspection, diagnostics, or testing.
     *
     * @return array<class-string, class-string[]>
     */
    public function all(): array
    {
        return $this->map->all();
    }
}
