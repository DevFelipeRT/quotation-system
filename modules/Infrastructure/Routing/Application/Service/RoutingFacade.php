<?php

declare(strict_types=1);

namespace Routing\Application\Service;

use Routing\Application\Contracts\RoutingServiceInterface;
use Routing\Infrastructure\Contracts\RouteRepositoryInterface;
use Routing\Presentation\Http\Contracts\HttpRouteInterface;
use Routing\Presentation\Http\Contracts\ServerRequestInterface;

/**
 * RoutingFacade
 *
 * High-level interface for consumers to interact with the routing system.
 * Provides simplified access to route execution, lookup, and emitted events.
 */
class RoutingFacade
{
    private RoutingServiceInterface $engine;
    private RouteRepositoryInterface $repository;

    /**
     * @param RoutingServiceInterface $engine
     * @param RouteRepositoryInterface $repository
     */
    public function __construct(
        RoutingServiceInterface $engine,
        RouteRepositoryInterface $repository
    ) {
        $this->engine = $engine;
        $this->repository = $repository;
    }

    /**
     * Handles a ServerRequest and returns the result of the routed controller.
     *
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function handle(ServerRequestInterface $request)
    {
        return $this->engine->handle($request);
    }

    /**
     * Returns all registered routes.
     *
     * @return HttpRouteInterface[]
     */
    public function allRoutes(): array
    {
        return $this->repository->all();
    }

    /**
     * Finds a route by its declared name.
     *
     * @param string $name
     * @return HttpRouteInterface|null
     */
    public function findRouteByName(string $name): ?HttpRouteInterface
    {
        return $this->repository->findByName($name);
    }

    /**
     * Returns the latest events emitted by the routing engine.
     * Optionally releases (clears) them.
     *
     * @param bool $release
     * @return RoutingEventInterface[]
     */
    public function events(bool $release = false): array
    {
        if (!$this->engine instanceof RoutingService) {
            return [];
        }
        if ($release) {
            return $this->engine->releaseEvents();
        }
        return $this->engine->recordedEvents();
    }
}
