<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure;
;

use App\Infrastructure\Routing\Infrastructure\Contracts\ControllerFactoryInterface;
use App\Shared\Container\Domain\Contracts\ContainerInterface;
use App\Infrastructure\Routing\Infrastructure\Exceptions\RouteDispatchException;
use App\Infrastructure\Routing\Infrastructure\Exceptions\InvalidRouteDefinitionException;
use ReflectionClass;
use ReflectionException;

/**
 * Concrete factory that creates controller instances, using the DI container for dependencies.
 */
final class ControllerFactory implements ControllerFactoryInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $controllerClass): object
    {
        if ($this->container->has($controllerClass)) {
            return $this->resolveFromContainer($controllerClass);
        }

        return $this->instantiateWithReflection($controllerClass);
    }

    /**
     * Resolves a controller from the container.
     *
     * @param string $controllerClass
     * @return object
     * @throws InvalidRouteDefinitionException
     * @throws RouteDispatchException
     */
    private function resolveFromContainer(string $controllerClass): object
    {
        try {
            $controller = $this->container->get($controllerClass);
        } catch (\Throwable $e) {
            throw new RouteDispatchException(
                "Error resolving controller from container: {$controllerClass}",
                ['controller_class' => $controllerClass, 'container_error' => $e->getMessage()],
                0,
                $e
            );
        }

        $this->assertIsObject($controller, $controllerClass);

        return $controller;
    }

    /**
     * Instantiates a controller using reflection and resolves its dependencies via the container.
     *
     * @param string $controllerClass
     * @return object
     * @throws InvalidRouteDefinitionException
     * @throws RouteDispatchException
     */
    private function instantiateWithReflection(string $controllerClass): object
    {
        try {
            if (!class_exists($controllerClass)) {
                throw new InvalidRouteDefinitionException(
                    "Controller class does not exist: {$controllerClass}",
                    ['controller_class' => $controllerClass]
                );
            }

            $reflection = new ReflectionClass($controllerClass);

            if (!$reflection->isInstantiable()) {
                throw new InvalidRouteDefinitionException(
                    "Controller class is not instantiable: {$controllerClass}",
                    ['controller_class' => $controllerClass]
                );
            }

            $constructor = $reflection->getConstructor();

            if (is_null($constructor) || $constructor->getNumberOfParameters() === 0) {
                return new $controllerClass();
            }

            $dependencies = $this->resolveConstructorDependencies($constructor->getParameters(), $controllerClass);

            return $reflection->newInstanceArgs($dependencies);

        } catch (InvalidRouteDefinitionException $e) {
            throw $e;
        } catch (ReflectionException $e) {
            throw new InvalidRouteDefinitionException(
                "Controller class could not be reflected: {$controllerClass}",
                ['controller_class' => $controllerClass, 'reflection_error' => $e->getMessage()]
            );
        } catch (\Throwable $e) {
            throw new RouteDispatchException(
                "Error creating controller instance for: {$controllerClass}",
                ['controller_class' => $controllerClass, 'creation_error' => $e->getMessage()],
                0,
                $e
            );
        }
    }

    /**
     * Resolves constructor dependencies using the container or default values.
     *
     * @param array $parameters
     * @param string $controllerClass
     * @return array
     * @throws InvalidRouteDefinitionException
     * @throws RouteDispatchException
     */
    private function resolveConstructorDependencies(array $parameters, string $controllerClass): array
    {
        $dependencies = [];
        foreach ($parameters as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin()) {
                try {
                    $dependencies[] = $this->container->get($type->getName());
                } catch (\Throwable $e) {
                    throw new RouteDispatchException(
                        "Failed to resolve constructor dependency '{$type->getName()}' for controller '{$controllerClass}'",
                        ['controller_class' => $controllerClass, 'param' => $param->getName(), 'container_error' => $e->getMessage()],
                        0,
                        $e
                    );
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
            } else {
                throw new InvalidRouteDefinitionException(
                    "Cannot resolve parameter \${$param->getName()} for {$controllerClass}",
                    ['controller_class' => $controllerClass, 'param' => $param->getName()]
                );
            }
        }
        return $dependencies;
    }

    /**
     * Asserts that the resolved controller is a valid object.
     *
     * @param mixed $controller
     * @param string $controllerClass
     * @throws RouteDispatchException
     */
    private function assertIsObject(mixed $controller, string $controllerClass): void
    {
        if (!is_object($controller)) {
            throw new RouteDispatchException(
                "Container did not return a valid controller instance for: {$controllerClass}",
                ['controller_class' => $controllerClass]
            );
        }
    }
}
