<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract;

/**
 * Defines a contract for immutable value objects that encapsulate data for renderable entities.
 *
 * This interface ensures that all implementations provide thread-safe, immutable access
 * to the underlying data structure while maintaining serialization capabilities.
 *
 * Although structurally identical to ViewDataInterface, this interface provides
 * semantic clarity for data containers specifically intended for renderable objects
 * such as pages, components, or view templates.
 */
interface RenderableDataInterface
{
    /**
     * Retrieves all encapsulated data as an associative array.
     *
     * @return array<string, mixed> Complete dataset with string keys and mixed values
     */
    public function all(): array;

    /**
     * Retrieves the value associated with the specified key.
     *
     * @param string $key The data key to retrieve
     * @return mixed The value associated with the key
     * @throws \InvalidArgumentException When the specified key does not exist
     */
    public function get(string $key): mixed;

    /**
     * Determines whether the specified key exists in the dataset.
     *
     * @param string $key The key to check for existence
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool;

    /**
     * Exports the encapsulated data as an array for serialization or external processing.
     *
     * @return array<string, mixed> Array representation of the data suitable for serialization
     */
    public function toArray(): array;
}
