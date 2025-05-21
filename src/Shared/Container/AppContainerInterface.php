<?php

declare(strict_types=1);

namespace App\Shared\Container;

/**
 * AppContainerInterface
 *
 * Defines the contract for the application's service container.
 * Supports registration, lazy instantiation, and dependency resolution.
 * Compatible with PSR-11 concepts, but decoupled from external dependencies.
 *
 * Features:
 * - Services and factories can be registered manually via `set()`.
 * - If a service is not found and the given `$id` is a class name,
 *   the container must attempt to instantiate it using Reflection,
 *   resolving constructor dependencies recursively by type-hint.
 * - Root dependencies (such as LoggerInterface) must be registered manually.
 */
interface AppContainerInterface
{
    /**
     * Retrieve a service or dependency by its identifier (class name or alias).
     * If no binding is registered, attempts to instantiate the class automatically,
     * resolving its dependencies via Reflection-based autowiring.
     *
     * @param string $id Class name or service alias.
     * @return mixed The resolved service instance.
     * @throws NotFoundException If the service cannot be resolved or autowired.
     */
    public function get(string $id);

    /**
     * Determine if the container can resolve or autowire the given identifier.
     * Returns true if a manual binding exists or the class can be autowired.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Register a service instance or a factory for the given identifier.
     * If $concrete is a callable, it will be used as a factory.
     * If $concrete is an object, it is registered as a singleton instance.
     *
     * @param string $id
     * @param callable|object $concrete
     * @return void
     */
    public function set(string $id, $concrete): void;
}
