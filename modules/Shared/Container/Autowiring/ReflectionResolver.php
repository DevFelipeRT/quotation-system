<?php

declare(strict_types=1);

namespace Container\Autowiring;

use Container\Contracts\ResolverInterface;
use Container\Exceptions\NotFoundException;
use Container\Exceptions\CircularDependencyException;
use PublicContracts\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;

/**
 * Class ReflectionResolver
 *
 * Implements reflection-based autowiring for resolving dependencies recursively.
 * Handles constructor injection for service classes that do not have explicit bindings,
 * managing the dependency resolution stack to ensure cycle detection.
 *
 * This resolver always attempts to resolve dependencies using explicit bindings first.
 * If no binding exists and the dependency is a valid class (FQCN), recursive autowiring is attempted.
 * If neither approach succeeds, a NotFoundException is thrown.
 *
 * @package Container\Infrastructure\Autowiring
 */
class ReflectionResolver implements ResolverInterface
{
    /**
     * Resolves a service instance via autowiring.
     * Uses PHP Reflection API to recursively resolve constructor dependencies.
     *
     * @param string $id                          Fully qualified class name of the service to resolve.
     * @param ContainerInterface $container       The container instance for explicit binding checks.
     * @param array $resolutionStack              Stack of service ids currently being resolved, for cycle detection.
     * @return mixed                              Instantiated service object.
     *
     * @throws NotFoundException                  If the class does not exist or cannot be resolved.
     * @throws CircularDependencyException        If a circular dependency is detected.
     */
    public function resolve(string $id, ContainerInterface $container, array $resolutionStack = []): mixed
    {
        if (in_array($id, $resolutionStack, true)) {
            throw new CircularDependencyException($id, [...$resolutionStack, $id]);
        }

        if (!class_exists($id)) {
            throw new NotFoundException($id);
        }

        $reflection = new ReflectionClass($id);

        $constructor = $reflection->getConstructor();
        if (is_null($constructor) || $constructor->getNumberOfParameters() === 0) {
            return $reflection->newInstance();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];
        foreach ($parameters as $param) {
            $dependency = $this->resolveParameter($param, $container, [...$resolutionStack, $id]);
            $dependencies[] = $dependency;
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Resolves a single constructor parameter via explicit binding or recursive autowiring.
     *
     * - Attempts to resolve via explicit binding (container).
     * - If not available, checks if the dependency is a valid class for recursive autowiring.
     * - Falls back to default value if available.
     * - Throws NotFoundException if the dependency cannot be resolved.
     *
     * @param ReflectionParameter $param          The constructor parameter to resolve.
     * @param ContainerInterface $container       The container instance for explicit binding checks.
     * @param array $resolutionStack              Stack of service ids currently being resolved, for cycle detection.
     * @return mixed                              The resolved dependency value or object.
     *
     * @throws NotFoundException                  If the dependency cannot be resolved.
     * @throws CircularDependencyException        If a circular dependency is detected.
     */
    protected function resolveParameter(ReflectionParameter $param, ContainerInterface $container, array $resolutionStack): mixed
    {
        $type = $param->getType();

        if ($type && !$type->isBuiltin()) {
            $dependencyClass = $type->getName();

            // First barrier: explicit binding resolution
            if ($container->has($dependencyClass)) {
                try {
                    return $container->get($dependencyClass);
                } catch (NotFoundException) {
                    // Fallback to autowiring if binding is missing at runtime
                }
            }

            // Second barrier: recursive autowiring for valid class names
            if (class_exists($dependencyClass)) {
                return $this->resolve($dependencyClass, $container, $resolutionStack);
            }

            throw new NotFoundException($dependencyClass);
        }

        // Use default parameter value if available
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        throw new NotFoundException($param->getName());
    }
}
