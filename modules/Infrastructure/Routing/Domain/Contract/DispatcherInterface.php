<?php

declare(strict_types=1);

namespace Routing\Domain\Contract;

use Routing\Domain\Contract\RouteInterface;

/**
 * Describes the capabilities of a dispatcher.
 *
 * The dispatcher is responsible for executing the handler of a matched route,
 * following design principles compatible with PSR standards for request handlers.
 */
interface DispatcherInterface
{
    /**
     * Dispatches the handler associated with a given route.
     *
     * @param RouteInterface $route The route object resulting from a successful match.
     *
     * @return mixed The result produced by the route's handler. The nature of
     * this result is application-specific (e.g., a string, an array, a
     * response object, etc.).
     *
     * @throws \Throwable if an error occurs during dispatching or handler execution.
     */
    public function dispatch(RouteInterface $route): mixed;
}