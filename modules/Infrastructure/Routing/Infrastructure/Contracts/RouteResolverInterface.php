<?php

declare(strict_types=1);

namespace Routing\Infrastructure\Contracts;

use Routing\Presentation\Http\Contracts\HttpRouteInterface;
use Routing\Presentation\Http\Contracts\ServerRequestInterface;

/**
 * Defines a contract for resolving HTTP routes based on incoming requests.
 * The implementation is responsible for both iterating over available routes
 * and determining if a given route matches the provided request.
 */
interface RouteResolverInterface
{
    /**
     * Resolves the first route that matches the provided request according to the defined matching criteria.
     *
     * @param ServerRequestInterface $request The HTTP request to resolve.
     * @return HttpRouteInterface|null The matching route, or null if no route matches the request.
     */
    public function resolve(ServerRequestInterface $request): ?HttpRouteInterface;
}
