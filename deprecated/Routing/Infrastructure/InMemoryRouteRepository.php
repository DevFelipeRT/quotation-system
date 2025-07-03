<?php

declare(strict_types=1);

namespace Routing\Infrastructure;

use Routing\Infrastructure\Contracts\RouteRepositoryInterface;
use Routing\Presentation\Http\Contracts\HttpRouteInterface;
use InvalidArgumentException;

/**
 * InMemoryRouteRepository
 *
 * A volatile, array-based implementation of the RouteRepositoryInterface.
 * Stores route declarations in memory, allowing for addition, retrieval,
 * and lookup by name at runtime.
 *
 * This implementation is suitable for testing, bootstrapping environments,
 * or systems that do not require persistent route storage.
 */
final class InMemoryRouteRepository implements RouteRepositoryInterface
{
    /**
     * @var HttpRouteInterface[]
     */
    private array $routes = [];

    /**
     * Registers a new route instance into memory.
     * Throws exception if a route with the same name or path+method exists.
     *
     * @param HttpRouteInterface $route
     * @return void
     * @throws InvalidArgumentException
     */
    public function add(HttpRouteInterface $route): void
    {
        $this->assertValidRoute($route);
        $this->assertNoDuplicateName($route);
        $this->assertNoDuplicatePathAndMethod($route);

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

    /**
     * Ensures that only valid route objects are added.
     *
     * @param mixed $route
     * @throws InvalidArgumentException
     */
    private function assertValidRoute(mixed $route): void
    {
        if (!$route instanceof HttpRouteInterface) {
            throw new InvalidArgumentException(
                'Invalid route type: must implement HttpRouteInterface.'
            );
        }
    }

    /**
     * Throws if a route with the same name already exists.
     *
     * @param HttpRouteInterface $route
     * @throws InvalidArgumentException
     */
    private function assertNoDuplicateName(HttpRouteInterface $route): void
    {
        foreach ($this->routes as $existing) {
            if ($existing->name() === $route->name()) {
                throw new InvalidArgumentException(
                    "A route with the name '{$route->name()}' is already registered."
                );
            }
        }
    }

    /**
     * Throws if a route with the same path and method already exists.
     *
     * @param HttpRouteInterface $route
     * @throws InvalidArgumentException
     */
    private function assertNoDuplicatePathAndMethod(HttpRouteInterface $route): void
    {
        foreach ($this->routes as $existing) {
            if (
                $existing->path()->equals($route->path()) &&
                $existing->method()->equals($route->method())
            ) {
                throw new InvalidArgumentException(
                    "A route with the path '{$route->path()->value()}' and method '{$route->method()->value()}' is already registered."
                );
            }
        }
    }
}
