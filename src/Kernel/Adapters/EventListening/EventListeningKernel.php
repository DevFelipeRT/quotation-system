<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\EventListening;

use App\Adapters\EventListening\Application\Resolver\EventListenerMap;
use App\Adapters\EventListening\Application\Resolver\EventListenerResolver;
use App\Adapters\EventListening\Infrastructure\Dispatcher\DefaultEventDispatcher;
use App\Kernel\Adapters\EventListening\Contracts\EventBindingProviderInterface;
use App\Shared\Container\AppContainerInterface;
use App\Shared\Discovery\DiscoveryScanner;

use App\Shared\Event\Contracts\EventDispatcherInterface;

/**
 * EventListeningKernel
 *
 * Centralizes all event-to-listener bindings using modular providers.
 * Providers are discovered automatically via namespace scanning.
 */
final class EventListeningKernel
{
    private EventListenerResolver $resolver;
    private EventDispatcherInterface $dispatcher;

    /**
     * Constructs and bootstraps the event listening kernel.
     * Uses DiscoveryScanner to find and resolve binding providers.
     *
     * @param AppContainerInterface $container
     */
    public function __construct(AppContainerInterface $container)
    {
        $scanner = new DiscoveryScanner();

        $providers = array_map(
            fn ($p) => $container->get($p::class),
            $scanner->discoverImplementing(
                EventBindingProviderInterface::class,
                'App\\Kernel\\Adapters\\EventListening\\Providers'
            )
        );

        $bindings = $this->mergeBindingsFromProviders($providers);
        $map = new EventListenerMap($bindings);

        $this->resolver = new EventListenerResolver($map, $container);
        $this->dispatcher = new DefaultEventDispatcher($this->resolver);
    }

    public function resolver(): EventListenerResolver
    {
        return $this->resolver;
    }

    public function dispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * Aggregates all event-listener class bindings from registered providers.
     *
     * @param EventBindingProviderInterface[] $providers
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
