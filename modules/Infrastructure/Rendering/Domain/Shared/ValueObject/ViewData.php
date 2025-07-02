<?php

declare(strict_types=1);

namespace Rendering\Domain\Shared\ValueObject;

use InvalidArgumentException;
use Rendering\Domain\Contract\ViewDataInterface;

/**
 * ViewData
 *
 * Immutable Value Object that encapsulates view data as an associative array.
 * Guarantees that the internal data structure cannot be mutated after construction.
 * Designed for delivery of data to the presentation layer (view/template).
 *
 * @package Rendering/Domain/ValueObjects
 */
final class ViewData implements ViewDataInterface
{
    /**
     * @var array<string, mixed>
     */
    private readonly array $data;

    /**
     * Initializes the ViewData object with an associative array.
     *
     * @param array<string, mixed> $data  Data to be delivered to the view.
     * @throws InvalidArgumentException   If the array is not associative.
     */
    public function __construct(array $data = [])
    {
        if (!self::isAssociative($data)) {
            throw new InvalidArgumentException('ViewData only accepts associative arrays.');
        }
        $this->data = self::deepCopy($data);
    }

    /**
     * Returns a deep copy of all encapsulated data.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return self::deepCopy($this->data);
    }

    /**
     * Returns the value for a given key, or throws if the key is not found.
     *
     * @param string $key
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        if (!array_key_exists($key, $this->data)) {
            throw new InvalidArgumentException("Key '{$key}' does not exist in ViewData.");
        }
        return $this->data[$key];
    }

    /**
     * Checks if a given key exists in ViewData.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Exports the data as an array (useful for serialization, debugging, etc.).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * Determines if an array is associative.
     *
     * @param array $arr
     * @return bool
     */
    private static function isAssociative(array $arr): bool
    {
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Performs a deep copy of the array (for immutability).
     *
     * @param array $array
     * @return array
     */
    private static function deepCopy(array $array): array
    {
        return unserialize(serialize($array));
    }
}
