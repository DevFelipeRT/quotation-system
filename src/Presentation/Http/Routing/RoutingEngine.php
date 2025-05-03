<?php

namespace App\Presentation\Http\Routing;

use App\Interfaces\Application\Routing\RoutingEngineInterface;
use App\Interfaces\Application\Routing\RouteResolverInterface;
use App\Interfaces\Presentation\Routing\RouteDispatcherInterface;
use App\Interfaces\Presentation\Routing\RouteRequestInterface;
use RuntimeException;

/**
 * RoutingEngine
 *
 * Coordinates the full HTTP routing workflow: request resolution, route matching,
 * and controller dispatching.
 *
 * @implements RoutingEngineInterface
 */
final class RoutingEngine implements RoutingEngineInterface
{
    public function __construct(
        private readonly RouteResolverInterface $resolver,
        private readonly RouteDispatcherInterface $dispatcher
    ) {}

    /**
     * Handles a full route resolution and dispatching workflow.
     *
     * @param RouteRequestInterface $request
     * @return mixed
     * @throws RuntimeException If no route matches the request.
     */
    public function handle(RouteRequestInterface $request): mixed
    {
        $route = $this->resolver->resolve($request);

        if ($route === null) {
            throw new RuntimeException("No route matched the given request: '{$request->path()}'.");
        }

        return $this->dispatcher->dispatch($request);
    }
}
