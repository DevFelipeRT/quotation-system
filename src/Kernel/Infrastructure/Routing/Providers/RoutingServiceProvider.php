<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure\Routing\Providers;

use App\Shared\Container\Domain\Contracts\ContainerInterface;
use App\Infrastructure\Routing\Infrastructure\Repository\InMemoryRouteRepository;
use App\Infrastructure\Routing\Infrastructure\Matcher\DefaultRouteMatcher;
use App\Infrastructure\Routing\Infrastructure\Registration\RouteRegistrar;
use App\Infrastructure\Routing\Infrastructure\Resolver\DefaultRouteResolver;
use App\Infrastructure\Routing\Infrastructure\Dispatcher\DefaultRouteDispatcher;
use App\Shared\Container\Domain\Contracts\ServiceProviderInterface;

/**
 * RoutingServiceProvider
 *
 * Registers all essential routing services and contracts in the container.
 * Promotes modularity and testability for the routing subsystem.
 */
final class RoutingServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers routing contracts and concrete implementations.
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function register(ContainerInterface $container): void
    {
        // Register route repository as singleton
        $container->singleton(
            InMemoryRouteRepository::class,
            fn () => new InMemoryRouteRepository()
        );

        // Register route matcher as singleton
        $container->singleton(
            DefaultRouteMatcher::class,
            fn () => new DefaultRouteMatcher()
        );

        // Register route registrar as singleton
        $container->singleton(
            RouteRegistrar::class,
            fn () => new RouteRegistrar([])
        );

        // Register route resolver as singleton
        $container->singleton(
            DefaultRouteResolver::class,
            fn () => new DefaultRouteResolver(
                $container->get(InMemoryRouteRepository::class),
                $container->get(DefaultRouteMatcher::class)
            )
        );

        // Register route dispatcher as singleton
        $container->singleton(
            DefaultRouteDispatcher::class,
            fn () => new DefaultRouteDispatcher(
                $container->get(DefaultRouteResolver::class),
                [] // controller map to be set at runtime or injected via a provider
            )
        );

    }
}
