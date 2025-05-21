<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\EventListening\Contracts;

/**
 * EventBindingProviderInterface
 *
 * Contract for declaring event-to-listener class bindings.
 * Each implementation should provide a mapping from event class names
 * to arrays of listener class names, not instances.
 *
 * This interface enables dynamic resolution and dependency injection
 * for event listeners by the kernel or event subsystem.
 */
interface EventBindingProviderInterface
{
    /**
     * Returns an array of event bindings, mapping event class names
     * to arrays of listener class names.
     *
     * Example:
     *   [
     *      SomeEvent::class => [
     *          SomeListener::class,
     *          AnotherListener::class,
     *      ],
     *   ]
     *
     * @return array<class-string, class-string[]>
     */
    public function bindings(): array;
}
