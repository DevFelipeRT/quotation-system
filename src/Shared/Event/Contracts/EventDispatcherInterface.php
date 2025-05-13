<?php

declare(strict_types=1);

namespace App\Shared\Event\Contracts;

/**
 * Defines a contract for dispatching domain or application events
 * to external listeners in a decoupled and testable way.
 *
 * This interface should be implemented by infrastructure mechanisms
 * responsible for delivering events to one or more interested handlers.
 */
interface EventDispatcherInterface
{
    /**
     * Dispatches a given event object to all relevant listeners.
     *
     * The dispatcher must guarantee type-safe delivery of the event to
     * listeners that are explicitly registered to handle the event's type.
     *
     * @param object $event A domain or application-level event to be dispatched.
     *
     * @return void
     */
    public function dispatch(object $event): void;
}
