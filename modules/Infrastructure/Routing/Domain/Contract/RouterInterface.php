<?php

declare(strict_types=1);

namespace Routing\Domain\Contract;

use Routing\Domain\Contract\RequestInterface;
use Routing\Domain\Contract\RouteInterface;

/**
 * Describes the capabilities of a router.
 *
 * The router is responsible for holding a collection of defined routes and
 * finding a match for a given request, following design principles
 * compatible with PSR standards.
 */
interface RouterInterface
{
    /**
     * Adds a new route to the router's collection.
     *
     * This method allows for a fluent interface by returning its own instance.
     *
     * @param string $method  The request method (e.g., 'GET', 'POST').
     * @param string $path    The URL path pattern (e.g., '/users/{id}').
     * @param mixed  $handler The handler to be executed for this route,
     * such as a callable or a controller class name.
     * @return self
     */
    public function add(string $method, string $path, mixed $handler): self;

    /**
     * Matches a given request against the collection of routes.
     *
     * @param RequestInterface $request The generic domain request object to match against.
     *
     * @return RouteInterface Returns a RouteInterface instance representing the successful match.
     *
     * @throws RouteNotFoundException    When no route matches the given path.
     * @throws MethodNotAllowedException When a route matches the path but not the request method.
     */
    public function match(RequestInterface $request): RouteInterface;
}