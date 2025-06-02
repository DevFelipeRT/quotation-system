<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure;

use App\Infrastructure\Routing\Infrastructure\Contracts\RouteRepositoryInterface;
use App\Infrastructure\Routing\Infrastructure\Contracts\RouteResolverInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\ServerRequestInterface;
use App\Infrastructure\Routing\Infrastructure\Exceptions\MethodNotAllowedException;
use App\Infrastructure\Routing\Infrastructure\Exceptions\RouteNotFoundException;

/**
 * Default implementation of RouteResolverInterface.
 * Responsible for iterating over all registered routes and returning the first route
 * that matches the provided HTTP request based on HTTP method and path equality.
 * Throws specialized exceptions when no route or method is found.
 */
class DefaultRouteResolver implements RouteResolverInterface
{
    /**
     * @var RouteRepositoryInterface
     */
    private RouteRepositoryInterface $repository;

    /**
     * DefaultRouteResolver constructor.
     *
     * @param RouteRepositoryInterface $repository The repository holding all registered routes.
     */
    public function __construct(RouteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Resolves the first route that matches both the HTTP method and path of the given request.
     * Throws a RouteNotFoundException if no route is found for the requested path.
     * Throws a MethodNotAllowedException if a route is found for the path but not for the method.
     *
     * @param ServerRequestInterface $request The HTTP request to be resolved.
     * @return HttpRouteInterface The matching route.
     * @throws RouteNotFoundException If no route is found for the given path.
     * @throws MethodNotAllowedException If the path exists but the HTTP method is not allowed.
     */
    public function resolve(ServerRequestInterface $request): HttpRouteInterface
    {
        $matchingPathRoutes = $this->findRoutesByPath($request);

        if (empty($matchingPathRoutes)) {
            $this->throwRouteNotFoundException($request);
        }

        $matchingRoute = $this->findRouteByMethod($matchingPathRoutes, $request);

        if ($matchingRoute === null) {
            $this->throwMethodNotAllowedException($matchingPathRoutes, $request);
        }
        
        return $matchingRoute;
    }

    /**
     * Finds all routes that match the given request's path.
     *
     * @param ServerRequestInterface $request
     * @return HttpRouteInterface[]
     */
    private function findRoutesByPath(ServerRequestInterface $request): array
    {
        $routes = $this->repository->all();
        $path = $request->path();

        $matching = [];
        foreach ($routes as $route) {
            if ($route->path()->equals($path)) {
                $matching[] = $route;
            }
        }
        return $matching;
    }

    /**
     * Returns the first route in the provided set that matches the given request's HTTP method.
     *
     * @param HttpRouteInterface[] $routes
     * @param ServerRequestInterface $request
     * @return HttpRouteInterface|null
     */
    private function findRouteByMethod(array $routes, ServerRequestInterface $request): ?HttpRouteInterface
    {
        $method = $request->method();
        foreach ($routes as $route) {
            if ($route->method()->equals($method)) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Throws a RouteNotFoundException with contextual information.
     *
     * @param ServerRequestInterface $request
     * @throws RouteNotFoundException
     */
    private function throwRouteNotFoundException(ServerRequestInterface $request): void
    {
        throw new RouteNotFoundException(
            sprintf('No route found for path: %s', (string) $request->path()),
            [
                'path' => (string) $request->path(),
                'method' => (string) $request->method(),
            ]
        );
    }

    /**
     * Throws a MethodNotAllowedException with contextual information.
     *
     * @param HttpRouteInterface[] $routes
     * @param ServerRequestInterface $request
     * @throws MethodNotAllowedException
     */
    private function throwMethodNotAllowedException(array $routes, ServerRequestInterface $request): void
    {
        $allowedMethods = array_map(
            fn($route) => (string)$route->method(),
            $routes
        );

        throw new MethodNotAllowedException(
            sprintf(
                'Method %s is not allowed for path: %s. Allowed methods: [%s]',
                (string)$request->method(),
                (string)$request->path(),
                implode(', ', $allowedMethods)
            ),
            [
                'path' => (string)$request->path(),
                'method' => (string)$request->method(),
                'allowed_methods' => $allowedMethods
            ]
        );
    }
}
