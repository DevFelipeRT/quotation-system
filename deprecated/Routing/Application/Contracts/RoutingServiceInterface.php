<?php

declare(strict_types=1);

namespace Routing\Application\Contracts;

use Routing\Presentation\Http\Contracts\ServerRequestInterface;

/**
 * Interface RoutingServiceInterface
 *
 * Defines the contract for executing the full HTTP routing workflow.
 */
interface RoutingServiceInterface
{
    /**
     * Handles a complete routing process from request to controller execution.
     *
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function handle(ServerRequestInterface $request): mixed;
}
