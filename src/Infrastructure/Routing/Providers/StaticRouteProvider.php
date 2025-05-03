<?php

namespace App\Infrastructure\Routing\Providers;

use App\Interfaces\Application\Routing\RouteRepositoryInterface;
use App\Interfaces\Presentation\Routing\HttpRouteInterface;
use App\Interfaces\Presentation\Routing\RouteProviderInterface;

/**
 * StaticRouteProvider
 *
 * Provides a predefined set of HTTP route declarations, and registers them into
 * the application's routing repository during application bootstrapping.
 *
 * This implementation is appropriate for unit tests, demo environments,
 * or as a base fallback provider in production systems.
 *
 * @implements RouteProviderInterface
 */
final class StaticRouteProvider implements RouteProviderInterface
{
    /**
     * @var HttpRouteInterface[]
     */
    private readonly array $routes;

    /**
     * @param HttpRouteInterface[] $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Registers the predefined routes into the routing repository.
     *
     * @param RouteRepositoryInterface $repository
     * @return void
     */
    public function registerRoutes(RouteRepositoryInterface $repository): void
    {
        foreach ($this->routes as $route) {
            $repository->add($route);
        }
    }
}
