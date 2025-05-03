<?php

namespace App\Interfaces\Application\Routing;

use App\Interfaces\Presentation\Routing\RouteRequestInterface;

/**
 * Interface RoutingEngineInterface
 *
 * Defines the contract for executing the full HTTP routing workflow.
 */
interface RoutingEngineInterface
{
    /**
     * Handles a complete routing process from request to controller execution.
     *
     * @param RouteRequestInterface $request
     * @return mixed
     */
    public function handle(RouteRequestInterface $request): mixed;
}
