<?php

namespace App\Infrastructure\Routing\Dispatcher;

use App\Infrastructure\Routing\Contracts\RouteDispatcherInterface;
use App\Infrastructure\Routing\Contracts\RouteResolverInterface;
use App\Presentation\Http\Routing\Contracts\RouteRequestInterface;
use RuntimeException;

/**
 * DefaultRouteDispatcher
 *
 * Dispatches the controller action from the resolved route,
 * using a predefined controller instance map (injected externally).
 */
final class DefaultRouteDispatcher implements RouteDispatcherInterface
{
    /**
     * @param RouteResolverInterface $resolver
     * @param array<class-string, object> $controllerMap
     */
    public function __construct(
        private readonly RouteResolverInterface $resolver,
        private readonly array $controllerMap
    ) {}

    public function dispatch(RouteRequestInterface $request): mixed
    {
        $route = $this->resolver->resolve($request);

        if ($route === null) {
            throw new RuntimeException('No route found for the given request.');
        }

        $controllerClass = $route->controllerAction()->controllerClass();
        $method = $route->controllerAction()->method();

        if (!isset($this->controllerMap[$controllerClass])) {
            throw new RuntimeException("Controller instance for {$controllerClass} not available.");
        }

        $controller = $this->controllerMap[$controllerClass];

        if (!method_exists($controller, $method)) {
            throw new RuntimeException("Method {$method} not found in controller {$controllerClass}.");
        }

        return $controller->$method($request);
    }
}
