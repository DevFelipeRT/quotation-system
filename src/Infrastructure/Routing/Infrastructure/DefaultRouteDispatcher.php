<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure;

use App\Infrastructure\Routing\Infrastructure\Contracts\RouteDispatcherInterface;
use App\Infrastructure\Routing\Infrastructure\Contracts\ControllerFactoryInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\ServerRequestInterface;
use App\Infrastructure\Routing\Infrastructure\Exceptions\RouteDispatchException;

/**
 * Default implementation of RouteDispatcherInterface.
 * Responsible for invoking the controller action defined in the provided resolved route.
 * Handles controller instantiation via an injected factory.
 */
class DefaultRouteDispatcher implements RouteDispatcherInterface
{
    private ControllerFactoryInterface $factory;

    /**
     * DefaultRouteDispatcher constructor.
     *
     * @param ControllerFactoryInterface $factory Factory responsible for providing controller instances.
     */
    public function __construct(ControllerFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Dispatches the given resolved route and HTTP request to the corresponding controller action.
     *
     * @param HttpRouteInterface $route
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws RouteDispatchException If the controller or action cannot be executed.
     */
    public function dispatch(HttpRouteInterface $route, ServerRequestInterface $request): mixed
    {
        $controllerClass = $route->controllerAction()->class();
        $controllerMethod = $route->controllerAction()->method();

        $controller = $this->createControllerInstance($controllerClass, $route->name());
        $this->assertControllerMethodExists($controller, $controllerClass, $controllerMethod, $route->name());

        return $this->invokeController($controller, $controllerClass, $controllerMethod, $route->name(), $request);
    }

    /**
     * Uses the factory to create the controller instance.
     *
     * @param string $controllerClass
     * @param string $routeName
     * @return object
     * @throws RouteDispatchException
     */
    private function createControllerInstance(string $controllerClass, string $routeName): object
    {
        try {
            return $this->factory->create($controllerClass);
        } catch (\Throwable $e) {
            throw new RouteDispatchException(
                sprintf('Controller instance could not be created for class: %s', $controllerClass),
                [
                    'controller_class' => $controllerClass,
                    'route_name' => $routeName,
                    'factory_error' => $e->getMessage(),
                ],
                0,
                $e
            );
        }
    }

    /**
     * Asserts that the controller has the required method.
     *
     * @param object $controller
     * @param string $controllerClass
     * @param string $controllerMethod
     * @param string $routeName
     * @throws RouteDispatchException
     */
    private function assertControllerMethodExists(
        object $controller,
        string $controllerClass,
        string $controllerMethod,
        string $routeName
    ): void {
        if (!method_exists($controller, $controllerMethod)) {
            throw new RouteDispatchException(
                sprintf(
                    'Controller method %s::%s does not exist for route "%s".',
                    $controllerClass,
                    $controllerMethod,
                    $routeName
                ),
                [
                    'controller_class' => $controllerClass,
                    'controller_method' => $controllerMethod,
                    'route_name' => $routeName,
                ]
            );
        }
    }

    /**
     * Invokes the controller method with the provided request.
     *
     * @param object $controller
     * @param string $controllerClass
     * @param string $controllerMethod
     * @param string $routeName
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws RouteDispatchException
     */
    private function invokeController(
        object $controller,
        string $controllerClass,
        string $controllerMethod,
        string $routeName,
        ServerRequestInterface $request
    ): mixed {
        try {
            return $controller->{$controllerMethod}($request);
        } catch (\Throwable $e) {
            throw new RouteDispatchException(
                sprintf(
                    'An error occurred while dispatching %s::%s for route "%s": %s',
                    $controllerClass,
                    $controllerMethod,
                    $routeName,
                    $e->getMessage()
                ),
                [
                    'controller_class' => $controllerClass,
                    'controller_method' => $controllerMethod,
                    'route_name' => $routeName,
                    'dispatch_error' => $e->getMessage(),
                    'previous' => $e,
                ],
                0,
                $e
            );
        }
    }
}
