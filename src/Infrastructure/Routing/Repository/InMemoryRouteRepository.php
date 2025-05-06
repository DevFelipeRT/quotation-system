<?php

namespace App\Infrastructure\Routing\Repository;

use App\Infrastructure\Routing\Contracts\RouteRepositoryInterface;
use App\Presentation\Http\Routing\Contracts\HttpRouteInterface;

/**
 * InMemoryRouteRepository
 *
 * A volatile, array-based implementation of the RouteRepositoryInterface.
 * Stores route declarations in memory, allowing for addition, retrieval,
 * and lookup by name at runtime.
 *
 * This implementation is suitable for testing, bootstrapping environments,
 * or systems that do not require persistent route storage.
 *
 * @implements RouteRepositoryInterface
 */
final class InMemoryRouteRepository implements RouteRepositoryInterface
{
    /**
     * @var HttpRouteInterface[]
     */
    private array $routes = [];

    /**
     * Registers a new route instance into memory.
     *
     * @param HttpRouteInterface $route
     * @return void
     */
    public function add(HttpRouteInterface $route): void
    {
        $this->routes[] = $route;
    }

    /**
     * Returns all routes currently stored in memory.
     *
     * @return HttpRouteInterface[]
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * Searches for a route by its declared name.
     *
     * @param string $name
     * @return HttpRouteInterface|null
     */
    public function findByName(string $name): ?HttpRouteInterface
    {
        foreach ($this->routes as $route) {
            if ($route->name() === $name) {
                return $route;
            }
        }

        return null;
    }
}
