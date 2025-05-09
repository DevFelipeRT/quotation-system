<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Resolver;

use App\Infrastructure\Routing\Domain\Contracts\RouteMatcherInterface;
use App\Infrastructure\Routing\Domain\Contracts\RouteRepositoryInterface;
use App\Infrastructure\Routing\Domain\Contracts\RouteResolverInterface;
use App\Infrastructure\Routing\Presentation\Http\Routing\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\Routing\Contracts\RouteRequestInterface;

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
