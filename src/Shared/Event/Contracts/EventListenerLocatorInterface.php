<?php

declare(strict_types=1);

namespace App\Shared\Event\Contracts;

use App\EventListening\Domain\Contracts\EventListenerInterface;

/**
 * Provides a contract for locating event listeners dynamically at runtime.
 *
 * Implementations must return only valid listeners for a given event instance.
 */
interface EventListenerLocatorInterface
{
    /**
     * Resolves all listeners applicable to the given event.
     *
     * @param object $event
     * @return iterable<EventListenerInterface>
     */
    public function listenersFor(object $event): iterable;
}
