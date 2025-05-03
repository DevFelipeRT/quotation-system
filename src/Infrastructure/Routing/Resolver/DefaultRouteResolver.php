<?php

namespace App\Infrastructure\Routing\Resolver;

use App\Interfaces\Application\Routing\RouteResolverInterface;
use App\Interfaces\Application\Routing\RouteMatcherInterface;
use App\Interfaces\Application\Routing\RouteRepositoryInterface;
use App\Interfaces\Presentation\Routing\HttpRouteInterface;
use App\Interfaces\Presentation\Routing\RouteRequestInterface;

/**
 * DefaultRouteResolver
 *
 * Attempts to resolve an HTTP route for a given request by iterating over
 * all registered routes and matching them using the provided matcher.
 *
 * @implements RouteResolverInterface
 */
final class DefaultRouteResolver implements RouteResolverInterface
{
    public function __construct(
        private readonly RouteRepositoryInterface $repository,
        private readonly RouteMatcherInterface $matcher
    ) {}

    /**
     * Resolves a matching route from the route repository.
     *
     * @param RouteRequestInterface $request
     * @return HttpRouteInterface|null
     */
    public function resolve(RouteRequestInterface $request): ?HttpRouteInterface
    {
        foreach ($this->repository->all() as $route) {
            if ($this->matcher->matches($route, $request)) {
                return $route;
            }
        }

        return null;
    }
}
