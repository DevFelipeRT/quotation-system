<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Matcher;

use App\Infrastructure\Routing\Domain\Contracts\RouteMatcherInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\RouteRequestInterface;

/**
 * DefaultRouteMatcher
 *
 * Determines whether a given route is compatible with an incoming HTTP request,
 * based on method, path, scheme and host.
 *
 * This implementation performs strict equality comparison using value objects.
 *
 * @implements RouteMatcherInterface
 */
final class DefaultRouteMatcher implements RouteMatcherInterface
{
    /**
     * Checks if the given route matches the provided request.
     *
     * @param HttpRouteInterface $route
     * @param RouteRequestInterface $request
     * @return bool
     */
    public function matches(
        HttpRouteInterface $route,
        RouteRequestInterface $request
    ): bool {
        return
            $route->method()->equals($request->method()) &&
            $route->path()->equals($request->path());
            // NOTE: Scheme and host matching can be added if required
            // $request->scheme() === 'https' && $request->host() === 'example.com';
    }
}
