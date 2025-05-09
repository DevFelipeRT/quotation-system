<?php

namespace App\Infrastructure\Routing\Domain\Contracts;

use App\Infrastructure\Routing\Presentation\Http\Routing\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\Routing\Contracts\RouteRequestInterface;

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
