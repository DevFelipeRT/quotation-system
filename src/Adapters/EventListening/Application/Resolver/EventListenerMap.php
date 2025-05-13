<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Application\Resolver;

use App\Adapters\EventListening\Domain\Contracts\EventListenerInterface;
use InvalidArgumentException;

/**
 * Represents an immutable and validated mapping of event classes to listeners.
 *
 * This map serves as the canonical registry of event bindings. It validates
 * all listeners at construction time and guarantees that only valid mappings
 * are retained and accessible throughout the application's lifecycle.
 */
final class EventListenerMap
{
    /**
     * @var array<class-string, EventListenerInterface[]>
     */
    private array $map = [];

    /**
     * @param array<class-string, iterable<EventListenerInterface>> $bindings
     */
    public function __construct(array $bindings)
    {
        $this->map = $this->buildValidatedMap($bindings);
    }

    /**
     * Returns all listeners associated with the provided event instance.
     *
     * @param object $event
     * @return EventListenerInterface[]
     */
    public function for(object $event): array
    {
        return $this->map[get_class($event)] ?? [];
    }

    /**
     * Returns the entire event-to-listener mapping.
     *
     * @return array<class-string, EventListenerInterface[]>
     */
    public function all(): array
    {
        return $this->map;
    }

    /**
     * Builds and validates the internal event-to-listener map.
     *
     * @param array<class-string, iterable<EventListenerInterface>> $bindings
     * @return array<class-string, EventListenerInterface[]>
     */
    private function buildValidatedMap(array $bindings): array
    {
        $map = [];

        foreach ($bindings as $eventClass => $listeners) {
            $map[$eventClass] = $this->validateListeners($eventClass, $listeners);
        }

        return $map;
    }

    /**
     * Ensures each listener is valid and conforms to the expected interface.
     *
     * @param string $eventClass
     * @param iterable $listeners
     * @return EventListenerInterface[]
     *
     * @throws InvalidArgumentException
     */
    private function validateListeners(string $eventClass, iterable $listeners): array
    {
        $validated = [];

        foreach ($listeners as $listener) {
            if (!$listener instanceof EventListenerInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid listener for event "%s": must implement EventListenerInterface, got %s.',
                    $eventClass,
                    is_object($listener) ? get_class($listener) : gettype($listener)
                ));
            }

            $validated[] = $listener;
        }

        return $validated;
    }
}
