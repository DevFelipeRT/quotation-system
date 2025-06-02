<?php

declare(strict_types=1);

namespace Routing\Infrastructure;

use Routing\Infrastructure\Contracts\RouteRepositoryInterface;
use Routing\Presentation\Http\Contracts\HttpRouteInterface;

/**
 * RouteRegistrar
 *
 * Registers a collection of externally constructed route instances
 * into the application's routing repository.
 *
 * Intended for testing, dynamic composition, or scenarios where routes
 * are resolved or built outside the routing module.
 */
final class RouteRegistrar
{
    /**
     * @var HttpRouteInterface[]
     */
    private readonly array $routes;

    /**
     * @param HttpRouteInterface[] $routes Route objects to be registered.
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Registers each provided route into the routing repository.
     *
     * @param RouteRepositoryInterface $repository The routing repository to populate.
     */
    public function register(RouteRepositoryInterface $repository): void
    {
        foreach ($this->routes as /** @var HttpRouteInterface */ $route) {
            $repository->add($route);
        }
    }
}
