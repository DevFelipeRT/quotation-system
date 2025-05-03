<?php

namespace App\Interfaces\Application\Routing;

use App\Interfaces\Presentation\Routing\HttpRouteInterface;

/**
 * Interface RouteRepositoryInterface
 *
 * Defines the contract for storing and retrieving HTTP routes.
 */
interface RouteRepositoryInterface
{
    /**
     * Adds a new route to the repository.
     *
     * @param HttpRouteInterface $route The route to be stored.
     * @return void
     */
    public function add(HttpRouteInterface $route): void;

    /**
     * Returns all registered routes.
     *
     * @return HttpRouteInterface[] Array of registered routes.
     */
    public function all(): array;

    /**
     * Finds a route by its name.
     *
     * @param string $name The name of the route.
     * @return HttpRouteInterface|null The route if found; otherwise, null.
     */
    public function findByName(string $name): ?HttpRouteInterface;
}
