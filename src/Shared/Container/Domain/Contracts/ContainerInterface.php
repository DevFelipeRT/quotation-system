<?php

declare(strict_types=1);

namespace App\Shared\Container\Domain\Contracts;

use App\Shared\Container\Domain\Exceptions\NotFoundException;
use App\Shared\Container\Domain\Exceptions\ContainerException;

/**
 * Interface ContainerInterface
 *
 * Contract for dependency injection containers inspired by PSR-11 (without Composer dependency).
 * Supports dynamic service bindings, singleton and transient lifecycles, and reflection-based autowiring.
 * Method signatures and exception semantics are compatible with the PSR-11 specification,
 * but there is no direct inheritance or reliance on external packages.
 *
 * @see https://www.php-fig.org/psr/psr-11/
 */
interface ContainerInterface
{
    /**
     * Registers a binding for the given identifier, optionally as a singleton.
     *
     * @param string   $id        Identifier (typically a class or interface name)
     * @param callable $factory   Factory responsible for producing the instance
     * @param bool     $singleton If true, the same instance will be reused (default: true)
     * @return void
     */
    public function bind(string $id, callable $factory, bool $singleton = true): void;

    /**
     * Registers a singleton binding (always reused).
     *
     * @param string   $id
     * @param callable $factory
     * @return void
     */
    public function singleton(string $id, callable $factory): void;

    /**
     * Resolves an instance by identifier.
     * If no explicit binding exists, attempts autowiring for class names.
     *
     * @param string $id
     * @return mixed
     * @throws NotFoundException    If the identifier cannot be resolved
     * @throws ContainerException   On any other container error
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
     * Removes a specific binding and its associated singleton instance.
     *
     * @param string $id
     * @return void
     */
    public function clear(string $id): void;

    /**
     * Clears all bindings and singleton instances.
     *
     * @return void
     */
    public function reset(): void;
}
