<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure;

use App\Infrastructure\Routing\Infrastructure\Contracts\RouteProviderInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\HttpRoute;
use App\Infrastructure\Routing\Domain\ValueObjects\HttpMethod;
use App\Infrastructure\Routing\Domain\ValueObjects\RoutePath;
use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Abstract base class for route providers.
 * Enforces a whitelist of allowed controllers and public methods.
 */
abstract class AbstractRouteProvider implements RouteProviderInterface
{
    /**
     * List of allowed controller class FQCNs.
     * Every subclass MUST override this property.
     * @var string[]
     */
    protected static array $allowedControllers = [];

    /**
     * Utility factory method to create a standardized HttpRoute instance.
     * Validates controller class and method for security.
     *
     * @param string $method           HTTP method (e.g., 'GET', 'POST').
     * @param string $path             Route path (e.g., '/items').
     * @param string $controllerClass  Controller class name.
     * @param string $controllerMethod Controller method name.
     * @param string $name             Route name.
     * @return HttpRouteInterface
     */
    protected function makeRoute(
        string $method,
        string $path,
        string $controllerClass,
        string $controllerMethod,
        string $name
    ): HttpRouteInterface {
        $this->assertControllersConfigured();
        $this->assertControllerAllowed($controllerClass);
        $this->assertControllerMethodPublic($controllerClass, $controllerMethod);

        return new HttpRoute(
            new HttpMethod($method),
            new RoutePath($path),
            new ControllerAction($controllerClass, $controllerMethod),
            $name
        );
    }

    /**
     * Throws if no allowed controllers are configured.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertControllersConfigured(): void
    {
        if (empty(static::$allowedControllers)) {
            throw new InvalidArgumentException(
                'No allowedControllers defined in ' . static::class . 
                '. Every provider must define an explicit list of allowed controllers.'
            );
        }
    }

    /**
     * Throws if the given controller is not in the allowedControllers list.
     *
     * @param string $controllerClass
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertControllerAllowed(string $controllerClass): void
    {
        if (!in_array($controllerClass, static::$allowedControllers, true)) {
            throw new InvalidArgumentException("Controller not allowed: {$controllerClass}");
        }
    }

    /**
     * Throws if the given controller method does not exist or is not public.
     *
     * @param string $controllerClass
     * @param string $controllerMethod
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertControllerMethodPublic(string $controllerClass, string $controllerMethod): void
    {
        try {
            $reflection = new ReflectionClass($controllerClass);

            if (!$reflection->hasMethod($controllerMethod)) {
                throw new InvalidArgumentException("Method '{$controllerMethod}' does not exist in {$controllerClass}");
            }

            $method = $reflection->getMethod($controllerMethod);

            if (!$method->isPublic()) {
                throw new InvalidArgumentException("Method '{$controllerMethod}' in {$controllerClass} is not public");
            }
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException(
                "Failed to reflect controller {$controllerClass}: {$e->getMessage()}"
            );
        }
    }

    /**
     * Returns all route definitions provided by this source.
     *
     * @return HttpRouteInterface[]
     */
    abstract public function provideRoutes(): array;
}
