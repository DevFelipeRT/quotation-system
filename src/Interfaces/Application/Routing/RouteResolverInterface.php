<?php

namespace App\Interfaces\Application\Routing;

use App\Interfaces\Presentation\Routing\HttpRouteInterface;
use App\Interfaces\Presentation\Routing\RouteRequestInterface;

/**
 * Interface RouteResolverInterface
 *
 * Defines the contract for resolving an HTTP route based on an incoming route request.
 */
interface RouteResolverInterface
{
    /**
     * Attempts to resolve the route matching the given request.
     *
     * @param RouteRequestInterface $request The request to be resolved.
     * @return HttpRouteInterface|null The matched route, or null if no match is found.
     */
    public function resolve(RouteRequestInterface $request): ?HttpRouteInterface;
}
