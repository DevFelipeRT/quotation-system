<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject\Shared;

use InvalidArgumentException;
use Rendering\Domain\Contract\RenderableDataInterface;

/**
 * RenderableData
 *
 * Immutable Value Object that encapsulates rendering data as an associative array.
 * Provides a secure, type-safe container for data intended for the presentation layer.
 * Guarantees immutability by preventing external mutation of the internal data structure
 * after instantiation, ensuring data integrity throughout the rendering pipeline.
 *
 * This implementation serves as a generic data carrier for various rendering contexts,
 * including views, templates, and other presentation components that require structured
 * data input while maintaining strict immutability constraints.
 *
 */
final class RenderableData implements RenderableDataInterface
{
    /**
     * Internal data storage container.
     * 
     * @var array<string, mixed> Associative array containing rendering data
     */
    private readonly array $data;

    /**
     * Constructs a new RenderableData instance with the provided associative array.
     * 
     * Validates that the input data is in associative array format and creates
     * an immutable deep copy to prevent external mutations. Empty arrays are
     * considered valid associative arrays.
     *
     * @param array<string, mixed> $data Associative array containing data for rendering contexts
     * @throws InvalidArgumentException When the provided array is not associative (indexed array)
     * 
     * @example
     * $data = new RenderableData(['title' => 'Page Title', 'user' => $userObject]);
     * $emptyData = new RenderableData(); // Valid - empty associative array
     */
    public function __construct(array $data = [])
    {
        if (!self::isAssociative($data)) {
            throw new InvalidArgumentException('RenderableData only accepts associative arrays.');
        }
        $this->data = self::deepCopy($data);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return self::deepCopy($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key): mixed
    {
        if (!array_key_exists($key, $this->data)) {
            throw new InvalidArgumentException("Key '{$key}' does not exist in RenderableData.");
        }
        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * Checks if the provided array is associative.
     *
     * @param array $arr The array to check
     * @return bool True if the array is associative, false otherwise
     */
    private static function isAssociative(array $arr): bool
    {
        if (empty($arr)) {
            return true; // An empty array is considered associative
        }
        // Check if the keys are not sequential integers starting from 0
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Creates a deep copy of the provided array structure.
     *
     * @param array $array The array structure to deep copy
     * @return array Complete deep copy of the input array
     * 
     * @internal This method is used internally for immutability enforcement
     */
    private static function deepCopy(array $array): array
    {
        return unserialize(serialize($array));
    }
}
