<?php

declare(strict_types=1);

namespace EventListening\Application\Resolver;

/**
 * Represents an immutable, validated mapping of event classes to listener class names.
 *
 * This map serves as the canonical registry of event bindings. It guarantees
 * only valid mappings are accessible throughout the application's lifecycle.
 * Listeners are resolved dynamically (via container/factory) at dispatch time.
 */
final class EventListenerMap
{
    /**
     * @var array<class-string, class-string[]>
     */
    private array $map = [];

    /**
     * @param array<class-string, class-string[]> $bindings
     */
    public function __construct(array $bindings)
    {
        $this->map = $this->buildValidatedMap($bindings);
    }

    /**
     * Returns all listener class names associated with the provided event instance.
     *
     * @param object $event
     * @return class-string[]
     */
    public function for(object $event): array
    {
        return $this->map[get_class($event)] ?? [];
    }

    /**
     * Returns the entire event-to-listener class mapping.
     *
     * @return array<class-string, class-string[]>
     */
    public function all(): array
    {
        return $this->map;
    }

    /**
     * Builds and validates the internal event-to-listener map.
     *
     * @param array<class-string, class-string[]> $bindings
     * @return array<class-string, class-string[]>
     */
    private function buildValidatedMap(array $bindings): array
    {
        $map = [];
        foreach ($bindings as $eventClass => $listenerClasses) {
            $map[$eventClass] = $this->validateListenerClasses($eventClass, $listenerClasses);
        }
        return $map;
    }

    /**
     * Ensures each listener class name is a valid, loadable class string.
     *
     * @param string $eventClass
     * @param iterable $listenerClasses
     * @return class-string[]
     *
     * @throws \InvalidArgumentException
     */
    private function validateListenerClasses(string $eventClass, iterable $listenerClasses): array
    {
        $validated = [];
        foreach ($listenerClasses as $listenerClass) {
            if (!is_string($listenerClass) || !class_exists($listenerClass)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid listener class for event "%s": got "%s", expected existing class name.',
                    $eventClass,
                    is_string($listenerClass) ? $listenerClass : gettype($listenerClass)
                ));
            }
            $validated[] = $listenerClass;
        }
        return $validated;
    }
}
