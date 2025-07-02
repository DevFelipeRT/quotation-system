<?php

declare(strict_types=1);

namespace EventListening\Domain\Contracts;

/**
 * Contract for event listeners that react to domain-level events.
 *
 * Each listener must be invokable and operate with side effects only.
 * Listeners must not return values or control application flow.
 */
interface EventListenerInterface
{
    /**
     * Reacts to a given event instance.
     *
     * @param object $event A domain or application-level event object.
     * @return void
     */
    public function __invoke(object $event): void;
}
