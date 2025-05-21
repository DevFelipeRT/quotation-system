<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\EventListening;

use App\Adapters\EventListening\Application\Resolver\EventListenerMap;
use App\Adapters\EventListening\Application\Resolver\EventListenerResolver;
use App\Adapters\EventListening\Infrastructure\Dispatcher\DefaultEventDispatcher;
use App\Kernel\Adapters\EventListening\Providers\DatabaseEventBindingProvider;
use App\Kernel\Adapters\EventListening\Providers\RoutingEventBindingProvider;
use App\Kernel\Adapters\EventListening\Providers\SessionEventBindingProvider;
use App\Shared\Container\AppContainerInterface;
use App\Shared\Event\Contracts\EventDispatcherInterface;

/**
 * EventListeningKernel
 *
 * Centralizes all event-to-listener bindings using modular providers.
 * Ensures every binding provider is registered and its listeners mapped.
 * Exposes the global event dispatcher and runtime event listener resolver.
 *
 * Providers are discovered and registered here.
 */
final class EventListeningKernel
{
    private EventListenerResolver $resolver;
    private EventDispatcherInterface $dispatcher;

    /**
     * Constructs and bootstraps the event listening kernel.
     * All known binding providers are instantiated and merged.
     *
     * @param AppContainerInterface $container
     */
    public function __construct(AppContainerInterface $container)
    {
        // Descoberta explícita dos providers (pode ser dinâmica, se desejar)
        $providers = [
            new DatabaseEventBindingProvider(),
            new RoutingEventBindingProvider(),
            new SessionEventBindingProvider(),
            // Adicione novos providers aqui conforme o sistema cresce.
        ];

        // Coleta todos os bindings como [evento => [nomeClasseListener, ...]]
        $bindings = $this->mergeBindingsFromProviders($providers);
        $map = new EventListenerMap($bindings);

        // Cria o resolver e dispatcher usando o container para DI nos listeners
        $this->resolver = new EventListenerResolver($map, $container);
        $this->dispatcher = new DefaultEventDispatcher($this->resolver);
    }

    /**
     * Returns the runtime event-to-listener resolver.
     *
     * @return EventListenerResolver
     */
    public function resolver(): EventListenerResolver
    {
        return $this->resolver;
    }

    /**
     * Returns the global event dispatcher for publishing events.
     *
     * @return EventDispatcherInterface
     */
    public function dispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * Aggregates all event-listener class bindings from registered providers.
     *
     * @param array $providers Array of EventBindingProviderInterface implementations.
     * @return array<class-string, class-string[]>
     */
    private function mergeBindingsFromProviders(array $providers): array
    {
        $bindings = [];
        foreach ($providers as $provider) {
            foreach ($provider->bindings() as $event => $listenerClasses) {
                $bindings[$event] = array_merge($bindings[$event] ?? [], $listenerClasses);
            }
        }
        return $bindings;
    }
}
