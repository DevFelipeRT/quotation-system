<?php

declare(strict_types=1);

namespace App\Shared\Container;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use App\Shared\Container\NotFoundException;

/**
 * AppContainer
 *
 * A lightweight PSR-agnostic dependency injection container with autowiring via reflection.
 * Supports singleton and transient bindings, and resolves unbound concrete classes automatically.
 */
final class AppContainer implements AppContainerInterface
{
    /** @var array<string, callable> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    public function bind(string $id, callable $factory, bool $singleton = true): void
    {
        $this->bindings[$id] = function () use ($factory, $singleton, $id) {
            $instance = $factory();
            if ($singleton) {
                $this->instances[$id] = $instance;
            }
            return $instance;
        };
    }

    public function singleton(string $id, callable $factory): void
    {
        $this->bind($id, $factory, true);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]) || class_exists($id);
    }

    public function clear(string $id): void
    {
        unset($this->bindings[$id], $this->instances[$id]);
    }

    public function reset(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            return ($this->bindings[$id])();
        }

        if (!class_exists($id)) {
            throw new NotFoundException("Service '{$id}' not found and not a class.");
        }

        return $this->autowire($id);
    }

    private function autowire(string $class): object
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new NotFoundException("Cannot reflect class '{$class}': {$e->getMessage()}");
        }

        if (!$reflection->isInstantiable()) {
            throw new NotFoundException("Class '{$class}' is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            $instance = new $class();
            $this->instances[$class] = $instance;
            return $instance;
        }

        $parameters = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                throw new NotFoundException("Cannot autowire parameter '{$param->getName()}' of class '{$class}'.");
            }

            $dependency = $type->getName();
            $parameters[] = $this->get($dependency);
        }

        $instance = $reflection->newInstanceArgs($parameters);
        $this->instances[$class] = $instance;

        return $instance;
    }
}
