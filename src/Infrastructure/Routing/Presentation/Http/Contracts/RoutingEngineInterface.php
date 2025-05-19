<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Presentation\Http\Contracts;

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
