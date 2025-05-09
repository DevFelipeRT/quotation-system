<?php

namespace App\Infrastructure\Routing\Domain\Contracts;

use App\Infrastructure\Routing\Presentation\Http\Routing\Contracts\RouteRequestInterface;

/**
 * Interface RouteDispatcherInterface
 *
 * Defines the contract for dispatching a resolved route request to its controller action.
 */
interface RouteDispatcherInterface
{
    /**
     * Dispatches the given HTTP route request to its corresponding controller.
     *
     * @param RouteRequestInterface $request The HTTP route request to be dispatched.
     * @return mixed The result of the controller execution (e.g., a Response object).
     */
    public function dispatch(RouteRequestInterface $request): mixed;
}
