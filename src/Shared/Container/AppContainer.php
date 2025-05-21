<?php

declare(strict_types=1);

namespace App\Shared\Container;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * AppContainer
 *
 * Minimal, extensible, and fully decoupled dependency injection container.
 * - Supports registration of singletons and factories (closures)
 * - Automatically instantiates and injects class dependencies via Reflection (autowiring)
 * - Enforces Single Responsibility Principle (SRP) for maintainability and clarity
 * - No external dependencies (pure PHP)
 */
class AppContainer implements AppContainerInterface
{
    /** @var array<string, object> Registered singleton services */
    private array $services = [];

    /** @var array<string, callable> Registered service factories (closures) */
    private array $factories = [];

    /**
     * Retrieve a service instance by identifier (class name or alias).
     * If not found, attempts to resolve via factory, or autowire the class recursively.
     *
     * @param string $id
     * @return mixed
     * @throws NotFoundException If the service cannot be resolved or autowired
     */
    public function get(string $id)
    {
        if ($this->hasService($id)) {
            return $this->getService($id);
        }

        if ($this->hasFactory($id)) {
            return $this->buildAndCacheFactory($id);
        }

        if ($this->isAutowirable($id)) {
            return $this->autowireAndCache($id);
        }

        throw new NotFoundException("Service not found or cannot be autowired: {$id}");
    }

    /**
     * Determine if the service can be resolved (registered or autowirable).
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->hasService($id)
            || $this->hasFactory($id)
            || $this->isAutowirable($id);
    }

    /**
     * Register a singleton instance or a factory (closure) for a given identifier.
     *
     * @param string $id
     * @param callable|object $concrete
     * @return void
     */
    public function set(string $id, $concrete): void
    {
        if (is_callable($concrete)) {
            $this->registerFactory($id, $concrete);
        } else {
            $this->registerService($id, $concrete);
        }
    }

    // === Service registry operations ===

    /** Check if a singleton service is registered. */
    private function hasService(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }

    /** Retrieve a registered singleton service. */
    private function getService(string $id)
    {
        return $this->services[$id];
    }

    /** Register a singleton service. */
    private function registerService(string $id, object $instance): void
    {
        $this->services[$id] = $instance;
    }

    // === Factory registry operations ===

    /** Check if a factory is registered for the identifier. */
    private function hasFactory(string $id): bool
    {
        return array_key_exists($id, $this->factories);
    }

    /** Register a factory (closure) for the identifier. */
    private function registerFactory(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /** Build a service using its factory, cache it, and return the instance. */
    private function buildAndCacheFactory(string $id)
    {
        $service = ($this->factories[$id])($this);
        $this->registerService($id, $service);
        return $service;
    }

    // === Autowiring & Reflection ===

    /**
     * Determine if a class can be autowired (exists and is instantiable).
     *
     * @param string $id
     * @return bool
     */
    private function isAutowirable(string $id): bool
    {
        if (!class_exists($id)) {
            return false;
        }
        try {
            $reflection = new ReflectionClass($id);
            return $reflection->isInstantiable();
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Autowire (instantiate with dependencies) and cache the instance.
     *
     * @param string $className
     * @return object
     * @throws NotFoundException
     */
    private function autowireAndCache(string $className): object
    {
        $instance = $this->autowire($className);
        $this->registerService($className, $instance);
        return $instance;
    }

    /**
     * Instantiate a class, resolving its constructor dependencies recursively.
     *
     * @param string $className
     * @return object
     * @throws NotFoundException
     */
    private function autowire(string $className): object
    {
        $reflection = $this->getReflection($className);

        if ($this->hasEmptyConstructor($reflection)) {
            return $reflection->newInstance();
        }

        $args = $this->resolveConstructorArguments($reflection);
        return $reflection->newInstanceArgs($args);
    }

    /**
     * Get a ReflectionClass instance for a class name.
     *
     * @param string $className
     * @return ReflectionClass
     * @throws NotFoundException
     */
    private function getReflection(string $className): ReflectionClass
    {
        try {
            return new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new NotFoundException("Cannot reflect class: {$className}", 0, $e);
        }
    }

    /** Check if a class has no constructor or only default arguments. */
    private function hasEmptyConstructor(ReflectionClass $reflection): bool
    {
        $constructor = $reflection->getConstructor();
        return is_null($constructor) || $constructor->getNumberOfParameters() === 0;
    }

    /**
     * Resolve all constructor dependencies recursively via parameter reflection.
     *
     * @param ReflectionClass $reflection
     * @return array
     * @throws NotFoundException
     */
    private function resolveConstructorArguments(ReflectionClass $reflection): array
    {
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            $args[] = $this->resolveParameter($parameter);
        }
        return $args;
    }

    /**
     * Resolve a constructor parameter by type-hint or default value.
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws NotFoundException
     */
    private function resolveParameter(ReflectionParameter $parameter)
    {
        $type = $parameter->getType();
        if ($type && !$type->isBuiltin()) {
            return $this->get($type->getName());
        }
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        throw new NotFoundException(
            "Cannot resolve parameter \${$parameter->getName()} for " .
            "{$parameter->getDeclaringClass()->getName()}::__construct()"
        );
    }
}
