<?php

namespace App\Kernel;

use App\Infrastructure\Routing\Dispatcher\DefaultRouteDispatcher;
use App\Infrastructure\Routing\Matcher\DefaultRouteMatcher;
use App\Infrastructure\Routing\Providers\HomeRouteProvider;
use App\Infrastructure\Routing\Providers\ItemRouteProvider;
use App\Infrastructure\Routing\Repository\InMemoryRouteRepository;
use App\Infrastructure\Routing\Resolver\DefaultRouteResolver;
use App\Presentation\Http\Routing\RoutingEngine;

/**
 * RouterKernel
 *
 * Responsible for bootstrapping the HTTP routing system. Registers routes,
 * binds route resolution logic, and composes the routing engine that
 * dispatches HTTP requests to the appropriate controller.
 */
final class RouterKernel
{
    private readonly RoutingEngine $engine;

    /**
     * @param array<class-string, object> $controllerMap
     */
    public function __construct(array $controllerMap)
    {
        $routeRepository = new InMemoryRouteRepository();

        // Route providers register route definitions
        (new HomeRouteProvider())->registerRoutes($routeRepository);
        (new ItemRouteProvider())->registerRoutes($routeRepository);

        $matcher    = new DefaultRouteMatcher();
        $resolver   = new DefaultRouteResolver($routeRepository, $matcher);
        $dispatcher = new DefaultRouteDispatcher($resolver, $controllerMap);

        $this->engine = new RoutingEngine($resolver, $dispatcher);
    }

    /**
     * Returns the routing engine responsible for request dispatch.
     */
    public function engine(): RoutingEngine
    {
        return $this->engine;
    }
}
