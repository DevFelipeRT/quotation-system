<?php

declare(strict_types=1);

namespace App\Shared\Event\Testing;

use App\Shared\Event\Contracts\EventListenerLocatorInterface;

/**
 * In-memory listener locator for testing or simplified bootstraps.
 *
 * Allows manual registration and resolution of listeners per event class.
 * Suitable for unit testing, lightweight prototypes, and non-container contexts.
 */
final class InMemoryEventListenerLocator implements EventListenerLocatorInterface
{
    /**
     * @var array<class-string, callable[]>
     */
    private array $listeners = [];

    /**
     * Registers a listener for the specified event class.
     *
     * @param string $eventClass
     * @param callable $listener
     * @return void
     */
    public function register(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    /**
     * Resolves all listeners applicable to the provided event instance.
     *
     * @param object $event
     * @return iterable<callable>
     */
    public function listenersFor(object $event): iterable
    {
        $eventClass = get_class($event);
        return $this->listeners[$eventClass] ?? [];
    }
}
