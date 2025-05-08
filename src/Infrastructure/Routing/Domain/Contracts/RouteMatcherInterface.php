<?php

namespace App\Infrastructure\Routing\Domain\Contracts;

/**
 * Interface RouteMatcherInterface
 *
 * Defines the contract for determining if a given route matches a specific request.
 */
interface RouteMatcherInterface
{
    /**
     * Determines whether the given route matches the given request.
     *
     * @param HttpRouteInterface $route   The route to evaluate.
     * @param RouteRequestInterface $request The request to match against.
     * @return bool True if the route matches the request; otherwise, false.
     */
    public function matches(HttpRouteInterface $route, RouteRequestInterface $request): bool;
}
