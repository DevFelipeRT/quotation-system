<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\Contracts;

use App\Adapters\EventListening\Domain\Contracts\EventListenerInterface;

/**
 * Interface for defining event-to-listener bindings.
 * Each implementation provides a mapping used by EventListenerMap.
 */
interface EventBindingProviderInterface
{
    /**
     * Returns the event bindings as an array mapping event classes
     * to arrays of listener instances.
     *
     * @return array<class-string, EventListenerInterface[]>
     */
    public function bindings(): array;
}
