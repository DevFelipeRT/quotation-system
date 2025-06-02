<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Contracts;

use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\ServerRequestInterface;

/**
 * Interface RouteDispatcherInterface
 *
 * Defines the contract for dispatching a resolved route to its controller action.
 */
interface RouteDispatcherInterface
{
    /**
     * Dispatches the given resolved route and HTTP request to the corresponding controller action.
     *
     * @param HttpRouteInterface $route   The resolved route to be dispatched.
     * @param ServerRequestInterface $request The HTTP request associated with the route.
     * @return mixed The result of the controller execution (e.g., a Response object).
     */
    public function dispatch(HttpRouteInterface $route, ServerRequestInterface $request): mixed;
}
