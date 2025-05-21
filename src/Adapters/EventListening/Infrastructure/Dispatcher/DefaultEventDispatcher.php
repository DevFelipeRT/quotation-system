<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\Dispatcher;

use App\Shared\Event\Contracts\EventDispatcherInterface;
use App\Shared\Event\Contracts\EventListenerLocatorInterface;
use App\Adapters\EventListening\Domain\Contracts\EventListenerInterface;
use InvalidArgumentException;

/**
 * DefaultEventDispatcher
 *
 * Dispatches events to all dynamically resolved listeners using SRP-compliant methods.
 * Listener resolution and execution are strictly separated for clarity, testability, and maintenance.
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
        $listeners = $this->resolveListeners($event);
        $this->invokeListeners($listeners, $event);
    }

    /**
     * Resolves all listeners for the given event using the locator.
     *
     * @param object $event
     * @return iterable<EventListenerInterface>
     */
    private function resolveListeners(object $event): iterable
    {
        return $this->locator->listenersFor($event);
    }

    /**
     * Invokes each listener with the provided event.
     *
     * @param iterable<EventListenerInterface> $listeners
     * @param object $event
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function invokeListeners(iterable $listeners, object $event): void
    {
        foreach ($listeners as $listener) {
            if (is_callable($listener)) {
                $listener($event);
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Listener resolved for event "%s" is not invokable: %s',
                    get_class($event),
                    is_object($listener) ? get_class($listener) : gettype($listener)
                ));
            }
        }
    }
}
