<?php

declare(strict_types=1);

namespace Routing\Infrastructure\Definition;

use Routing\Domain\ValueObject\Definition\Handler;
use Routing\Domain\ValueObject\Definition\PathPattern;
use Routing\Domain\ValueObject\Definition\RouteCollection;
use Routing\Domain\ValueObject\Definition\RouteDefinition;
use Routing\Domain\ValueObject\Definition\Verb;

/**
 * Provides a fluent API to define and collect application routes.
 *
 * This class acts as the primary interface for route configuration,
 * abstracting the creation of underlying Value Objects and managing
 * the route collection.
 */
final class RouteCollector
{
    private RouteCollection $routeCollection;
    private string $groupPrefix = '';

    public function __construct()
    {
        $this->routeCollection = new RouteCollection();
    }

    /**
     * Adds a route for the GET method.
     *
     * @param string $path    The path pattern for the route.
     * @param mixed  $handler The handler to be executed.
     * @return self
     */
    public function get(string $path, mixed $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    /**
     * Adds a route for the POST method.
     *
     * @param string $path    The path pattern for the route.
     * @param mixed  $handler The handler to be executed.
     * @return self
     */
    public function post(string $path, mixed $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    /**
     * Adds a route for the PUT method.
     *
     * @param string $path    The path pattern for the route.
     * @param mixed  $handler The handler to be executed.
     * @return self
     */
    public function put(string $path, mixed $handler): self
    {
        return $this->add('PUT', $path, $handler);
    }

    /**
     * Adds a route for the PATCH method.
     *
     * @param string $path    The path pattern for the route.
     * @param mixed  $handler The handler to be executed.
     * @return self
     */
    public function patch(string $path, mixed $handler): self
    {
        return $this->add('PATCH', $path, $handler);
    }

    /**
     * Adds a route for the DELETE method.
     *
     * @param string $path    The path pattern for the route.
     * @param mixed  $handler The handler to be executed.
     * @return self
     */
    public function delete(string $path, mixed $handler): self
    {
        return $this->add('DELETE', $path, $handler);
    }

    /**
     * Adds a route with a specified verb.
     *
     * This is the base method for route registration. It takes raw inputs
     * and converts them into the domain's value objects.
     *
     * @param string $verb    The request verb (e.g., 'GET', 'CLI').
     * @param string $path    The path pattern for the route.
     * @param mixed  $handler The handler to be executed.
     * @return self
     */
    public function add(string $verb, string $path, mixed $handler): self
    {
        $fullPath = $this->groupPrefix . $path;

        $routeDefinition = new RouteDefinition(
            new Verb($verb),
            new PathPattern($fullPath),
            new Handler($handler)
        );

        $this->routeCollection = $this->routeCollection->add($routeDefinition);

        return $this;
    }

    /**
     * Groups a set of routes under a common path prefix.
     *
     * @param string   $prefix         The prefix for the route group.
     * @param callable $routesCallback A callable that receives the collector instance.
     */
    public function group(string $prefix, callable $routesCallback): void
    {
        $previousPrefix = $this->groupPrefix;
        $this->groupPrefix .= $prefix;

        $routesCallback($this);

        $this->groupPrefix = $previousPrefix;
    }

    /**
     * Returns the final, immutable collection of all defined routes.
     *
     * @return RouteCollection
     */
    public function getCollection(): RouteCollection
    {
        return $this->routeCollection;
    }
}