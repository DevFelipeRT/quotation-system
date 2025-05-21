<?php

declare(strict_types=1);

namespace App\Shared\Container;

/**
 * Interface AppContainerInterface
 *
 * Defines the contract for dependency containers capable of managing service bindings,
 * singleton instances, and reflective autowiring.
 */
interface AppContainerInterface
{
    /**
     * Registers a binding with optional singleton behavior.
     *
     * @param string $id Identifier (usually a class/interface name)
     * @param callable $factory Factory function to resolve the instance
     * @param bool $singleton Whether the instance should be reused
     */
    public function bind(string $id, callable $factory, bool $singleton = true): void;

    /**
     * Registers a singleton binding (always reused).
     *
     * @param string $id
     * @param callable $factory
     */
    public function singleton(string $id, callable $factory): void;

    /**
     * Resolves a service instance by identifier.
     * Supports autowiring fallback for classes not explicitly bound.
     *
     * @param string $id
     * @return mixed
     * @throws \App\Shared\Container\NotFoundException
     */
    public function get(string $id): mixed;

    /**
     * Checks if a binding exists for the given identifier.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Removes a specific binding and instance.
     *
     * @param string $id
     */
    public function clear(string $id): void;

    /**
     * Clears all bindings and singleton instances.
     */
    public function reset(): void;
}