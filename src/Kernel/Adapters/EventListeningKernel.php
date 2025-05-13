<?php

declare(strict_types=1);

namespace App\Kernel\Adapters;

use App\Adapters\EventListening\Application\Resolver\EventListenerMap;
use App\Adapters\EventListening\Application\Resolver\EventListenerResolver;
use App\Kernel\Adapters\Contracts\EventBindingProviderInterface;
use App\Adapters\EventListening\Domain\Contracts\EventListenerInterface;

/**
 * EventListeningKernel
 *
 * Orchestrates and registers event-to-listener bindings from multiple providers.
 * Designed for modular integration of listener mappings per domain context.
 */
final class EventListeningKernel
{
    /**
     * @var EventListenerResolver
     */
    private EventListenerResolver $resolver;

    /**
     * Initializes the resolver by aggregating all event bindings.
     *
     * @param EventBindingProviderInterface[] $providers
     */
    public function __construct(array $providers)
    {
        $bindings = $this->mergeBindingsFromProviders($providers);
        $map = new EventListenerMap($bindings);
        $this->resolver = new EventListenerResolver($map);
    }

    /**
     * Returns the active event-to-listener resolver.
     *
     * @return EventListenerResolver
     */
    public function resolver(): EventListenerResolver
    {
        return $this->resolver;
    }

    /**
     * Aggregates all bindings provided by registered providers.
     *
     * @param EventBindingProviderInterface[] $providers
     * @return array<class-string, EventListenerInterface[]>
     */
    private function mergeBindingsFromProviders(array $providers): array
    {
        $bindings = [];

        foreach ($providers as $provider) {
            $this->mergeProviderBindings($bindings, $provider);
        }

        return $bindings;
    }

    /**
     * Appends a provider’s bindings into the shared binding array.
     *
     * @param array<class-string, EventListenerInterface[]> &$bindings
     * @param EventBindingProviderInterface $provider
     */
    private function mergeProviderBindings(array &$bindings, EventBindingProviderInterface $provider): void
    {
        foreach ($provider->bindings() as $event => $listeners) {
            $bindings[$event] = $this->mergeEventListeners($bindings[$event] ?? [], $listeners);
        }
    }

    /**
     * Combines multiple listener arrays for a given event type.
     *
     * @param EventListenerInterface[] $existing
     * @param EventListenerInterface[] $additional
     * @return EventListenerInterface[]
     */
    private function mergeEventListeners(array $existing, array $additional): array
    {
        return array_merge($existing, $additional);
    }
}
