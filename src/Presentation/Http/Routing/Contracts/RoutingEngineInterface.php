<?php

namespace App\Presentation\Http\Routing\Contracts;

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
