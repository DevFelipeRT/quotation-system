<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract;

/**
 * ViewDataInterface
 *
 * Contract for immutable value objects representing view data.
 * Guarantees that all implementations provide safe, immutable, and
 * serializable access to the underlying data structure.
 *
 * Designed for use in value objects delivered to views or template engines.
 *
 * @author
 */
interface ViewDataInterface
{
    /**
     * Returns all encapsulated view data as an associative array.
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * Returns the value for a given key.
     *
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException If the key does not exist.
     */
    public function get(string $key): mixed;

    /**
     * Checks whether a given key exists in the view data.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Exports the view data as an array (for serialization, testing, etc).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
