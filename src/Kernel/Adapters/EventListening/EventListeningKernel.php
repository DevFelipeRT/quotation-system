<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\EventListening;

use App\Adapters\EventListening\Application\Resolver\EventListenerMap;
use App\Adapters\EventListening\Application\Resolver\EventListenerResolver;
use App\Adapters\EventListening\Infrastructure\Dispatcher\DefaultEventDispatcher;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;
use App\Kernel\Adapters\EventListening\Contracts\EventBindingProviderInterface;
use App\Kernel\Discovery\DiscoveryKernel;
use App\Shared\Container\Domain\Contracts\ContainerInterface;
use App\Shared\Event\Contracts\EventDispatcherInterface;

/**
 * EventListeningKernel
 *
 * Responsible for discovering, validating, and aggregating event-to-listener bindings
 * via modular providers. This kernel builds the listener map, resolver, and dispatcher,
 * ensuring all dependencies are injected and validated upfront. Listeners are resolved
 * dynamically at dispatch time via the dependency container.
 */
final class EventListeningKernel
{
    private EventListenerResolver $resolver;
    private EventDispatcherInterface $dispatcher;

    /**
     * EventListeningKernel constructor.
     *
     * @param ContainerInterface      $container Dependency injection container.
     * @param DiscoveryKernel         $discovery Kernel responsible for discovering providers.
     * @param PsrLoggerInterface|null $logger    Optional logger for binding audit/debugging.
     *
     * @throws \RuntimeException If any discovered provider does not implement the required interface.
     */
    public function __construct(
        ContainerInterface $container,
        DiscoveryKernel $discovery,
        ?PsrLoggerInterface $logger = null
    ) {
        $providerFqcns = $this->discoverProviderFqcns($discovery);
        $providers = $this->instantiateProviders($container, $providerFqcns);
        $bindings = $this->collectBindings($providers);
        $deduplicatedBindings = $this->deduplicateBindings($bindings);

        $this->logBindings($logger, $deduplicatedBindings);

        $this->resolver = $this->createResolver($container, $deduplicatedBindings);
        $this->dispatcher = new DefaultEventDispatcher($this->resolver);
    }

    /**
     * Returns the event listener resolver.
     *
     * @return EventListenerResolver
     */
    public function resolver(): EventListenerResolver
    {
        return $this->resolver;
    }

    /**
     * Returns the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    public function dispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * Discovers fully qualified class names (FQCN) for all providers implementing
     * the EventBindingProviderInterface in the designated namespace.
     *
     * @param DiscoveryKernel $discovery Discovery kernel instance.
     * @return string[] List of FQCNs as strings.
     */
    private function discoverProviderFqcns(DiscoveryKernel $discovery): array
    {
        $fqcnObjects = $discovery->discoverImplementing(
            EventBindingProviderInterface::class,
            'App\\Kernel\\Adapters\\EventListening\\Providers'
        );

        return $this->extractFqcnStrings($fqcnObjects);
    }

    /**
     * Extracts FQCN strings from a collection of value objects.
     *
     * @param iterable $fqcnObjects
     * @return string[] List of FQCNs.
     */
    private function extractFqcnStrings(iterable $fqcnObjects): array
    {
        $fqcns = [];
        foreach ($fqcnObjects as $fqcnObj) {
            $fqcns[] = $fqcnObj->value();
        }
        return $fqcns;
    }

    /**
     * Instantiates provider objects via the container, validating contract.
     *
     * @param ContainerInterface $container Dependency injection container.
     * @param string[] $fqcns List of provider FQCNs.
     * @return EventBindingProviderInterface[]
     *
     * @throws \RuntimeException If a resolved class does not implement the required interface.
     */
    private function instantiateProviders(ContainerInterface $container, array $fqcns): array
    {
        $providers = [];
        foreach ($fqcns as $fqcn) {
            $provider = $container->get($fqcn);
            $this->assertIsEventBindingProvider($provider, $fqcn);
            $providers[] = $provider;
        }
        return $providers;
    }

    /**
     * Asserts that a resolved provider implements the EventBindingProviderInterface.
     *
     * @param mixed $provider The resolved provider instance.
     * @param string $fqcn The FQCN attempted.
     * @return void
     *
     * @throws \RuntimeException If the provider does not implement the required interface.
     */
    private function assertIsEventBindingProvider($provider, string $fqcn): void
    {
        if (!$provider instanceof EventBindingProviderInterface) {
            throw new \RuntimeException(
                "Class '$fqcn' does not implement EventBindingProviderInterface."
            );
        }
    }

    /**
     * Collects all event-to-listener bindings from providers.
     *
     * @param EventBindingProviderInterface[] $providers
     * @return array<class-string, class-string[]>
     */
    private function collectBindings(array $providers): array
    {
        $bindings = [];
        foreach ($providers as $provider) {
            $this->mergeProviderBindings($bindings, $provider->bindings());
        }
        return $bindings;
    }

    /**
     * Merges a single provider's bindings into the aggregate bindings array.
     *
     * @param array<class-string, class-string[]> $aggregate Reference to aggregate bindings.
     * @param array<class-string, class-string[]> $providerBindings
     * @return void
     */
    private function mergeProviderBindings(array &$aggregate, array $providerBindings): void
    {
        foreach ($providerBindings as $event => $listenerClasses) {
            if (!isset($aggregate[$event])) {
                $aggregate[$event] = [];
            }
            $aggregate[$event] = array_merge($aggregate[$event], $listenerClasses);
        }
    }

    /**
     * Deduplicates listener class names for each event.
     *
     * @param array<class-string, class-string[]> $bindings
     * @return array<class-string, class-string[]>
     */
    private function deduplicateBindings(array $bindings): array
    {
        foreach ($bindings as $event => &$listeners) {
            $listeners = array_values(array_unique($listeners));
        }
        unset($listeners); // Clean reference
        return $bindings;
    }

    /**
     * Logs the deduplicated bindings, if logger is provided.
     *
     * @param PsrLoggerInterface|null $logger Logger instance or null.
     * @param array<class-string, class-string[]> $bindings Listener bindings.
     * @return void
     */
    private function logBindings(?PsrLoggerInterface $logger, array $bindings): void
    {
        if ($logger !== null) {
            $logger->info('Event listener bindings loaded.', ['bindings' => $bindings]);
        }
    }

    /**
     * Builds the EventListenerResolver instance from bindings and the container.
     *
     * @param ContainerInterface $container
     * @param array<class-string, class-string[]> $bindings
     * @return EventListenerResolver
     */
    private function createResolver(
        ContainerInterface $container,
        array $bindings
    ): EventListenerResolver {
        $listenerMap = new EventListenerMap($bindings);
        $resolver = new EventListenerResolver($listenerMap, $container);
        return $resolver;
    }
}
