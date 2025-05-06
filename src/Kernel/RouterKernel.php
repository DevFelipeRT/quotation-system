<?php

namespace App\Kernel;

use App\Infrastructure\Routing\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Contracts\RouteProviderInterface;
use App\Infrastructure\Routing\Dispatcher\DefaultRouteDispatcher;
use App\Infrastructure\Routing\Matcher\DefaultRouteMatcher;
use App\Infrastructure\Routing\Providers\HomeRouteProvider;
use App\Infrastructure\Routing\Providers\ItemRouteProvider;
use App\Infrastructure\Routing\Registration\RouteRegistrar;
use App\Infrastructure\Routing\Repository\InMemoryRouteRepository;
use App\Infrastructure\Routing\Resolver\DefaultRouteResolver;
use App\Presentation\Http\Routing\RoutingEngine;

/**
 * Class RouterKernel
 *
 * Coordinates the initialization of the HTTP routing system by:
 * - Registering route providers
 * - Gathering and registering routes
 * - Constructing resolver and dispatcher
 * - Exposing the fully prepared RoutingEngine
 */
final class RouterKernel
{
    private RoutingEngine $engine;

    /**
     * @var RouteProviderInterface[]
     */
    private array $providers;

    /**
     * Initializes the routing engine with a controller map.
     *
     * @param array<class-string, object> $controllerMap Controller instances used in dispatching.
     */
    public function __construct(array $controllerMap)
    {
        $this->providers = $this->loadDefaultProviders();
        $this->engine = $this->initializeRoutingEngine($controllerMap);
    }

    /**
     * Returns the fully configured routing engine.
     */
    public function engine(): RoutingEngine
    {
        return $this->engine;
    }

    /**
     * Loads the default route providers registered in the system.
     *
     * @return RouteProviderInterface[]
     */
    private function loadDefaultProviders(): array
    {
        return [
            new HomeRouteProvider(),
            new ItemRouteProvider(),
        ];
    }

    /**
     * Initializes the RoutingEngine with all necessary components.
     *
     * @param array<class-string, object> $controllerMap
     */
    private function initializeRoutingEngine(array $controllerMap): RoutingEngine
    {
        $routes = $this->collectRoutesFromProviders($this->providers);
        $repository = $this->registerRoutes($routes);
        $resolver = $this->createResolver($repository);
        $dispatcher = $this->createDispatcher($resolver, $controllerMap);

        return new RoutingEngine($resolver, $dispatcher);
    }

    /**
     * Collects all routes from the provided route providers.
     *
     * @param RouteProviderInterface[] $providers
     * @return HttpRouteInterface[]
     */
    private function collectRoutesFromProviders(array $providers): array
    {
        $routes = [];

        foreach ($providers as $provider) {
            $routes = array_merge($routes, $provider->provideRoutes());
        }

        return $routes;
    }

    /**
     * Registers the provided routes into an in-memory repository.
     *
     * @param HttpRouteInterface[] $routes
     */
    private function registerRoutes(array $routes): InMemoryRouteRepository
    {
        $repository = new InMemoryRouteRepository();
        $registrar = new RouteRegistrar($routes);
        $registrar->register($repository);

        return $repository;
    }

    /**
     * Creates the route resolver with a default matcher.
     */
    private function createResolver(InMemoryRouteRepository $repository): DefaultRouteResolver
    {
        $matcher = new DefaultRouteMatcher();
        return new DefaultRouteResolver($repository, $matcher);
    }

    /**
     * Creates the dispatcher responsible for resolving and executing controllers.
     *
     * @param DefaultRouteResolver $resolver
     * @param array<class-string, object> $controllerMap
     */
    private function createDispatcher(DefaultRouteResolver $resolver, array $controllerMap): DefaultRouteDispatcher
    {
        return new DefaultRouteDispatcher($resolver, $controllerMap);
    }
}
