<?php

namespace App\Interfaces\Presentation\Routing;

use App\Application\Routing\RoutePath;
use App\Presentation\Http\Routing\HttpMethod;

/**
 * Interface RouteRequestInterface
 *
 * Represents the contract for an HTTP-based route request used by the routing system.
 */
interface RouteRequestInterface
{
    /**
     * Returns the HTTP method (e.g., GET, POST).
     *
     * @return HttpMethod
     */
    public function method(): HttpMethod;

    /**
     * Returns the route path of the HTTP request (e.g., "/users/42").
     *
     * @return RoutePath
     */
    public function path(): RoutePath;

    /**
     * Returns the host name of the HTTP request (e.g., "api.example.com").
     *
     * @return string
     */
    public function host(): string;

    /**
     * Returns the scheme of the HTTP request (e.g., "https").
     *
     * @return string
     */
    public function scheme(): string;

    /**
     * Compares this route request with another for structural equivalence.
     *
     * @param RouteRequestInterface $other
     * @return bool
     */
    public function equals(RouteRequestInterface $other): bool;
}
