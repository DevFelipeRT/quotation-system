<?php

declare(strict_types=1);

namespace Container\Infrastructure\Contracts;

/**
 * Interface ContainerScopeInterface
 *
 * Contract for defining resolution scopes (lifetime management) within the container.
 */
interface ContainerScopeInterface
{
    /**
     * Resolves the instance for the given identifier and factory, according to scope rules.
     *
     * @param string $id
     * @param callable $factory
     * @return mixed
     */
    public function resolve(string $id, callable $factory): mixed;

    /**
     * Clears any stored instance (if applicable).
     *
     * @param string $id
     * @return void
     */
    public function clear(string $id): void;

    /**
     * Clears all stored instances in this scope.
     *
     * @return void
     */
    public function clearAll(): void;
}
