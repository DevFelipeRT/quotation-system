<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Match;

/**
 * Represents the collection of parameters extracted from a URI during routing.
 *
 * As a First-Class Collection, this value object provides a typesafe and
 * expressive API for accessing route parameters, preventing issues common
 * with raw array manipulation.
 */
final class Parameters
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private readonly array $values = []
    ) {
    }

    /**
     * Retrieves a parameter value by its name.
     *
     * @param string $name The name of the parameter.
     * @param mixed|null $default The default value to return if the parameter is not found.
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->values[$name] ?? $default;
    }

    /**
     * Retrieves a parameter value cast to a string.
     *
     * @param string $name
     * @return string
     * @throws \InvalidArgumentException if the parameter is not found or cannot be cast to string.
     */
    public function getString(string $name): string
    {
        if (!isset($this->values[$name])) {
            throw new \InvalidArgumentException("Parameter '{$name}' not found.");
        }

        return (string) $this->values[$name];
    }

    /**
     * Retrieves a parameter value cast to an integer.
     *
     * @param string $name
     * @return int
     * @throws \InvalidArgumentException if the parameter is not found or is not numeric.
     */
    public function getInt(string $name): int
    {
        if (!isset($this->values[$name])) {
            throw new \InvalidArgumentException("Parameter '{$name}' not found.");
        }

        if (!is_numeric($this->values[$name])) {
            throw new \InvalidArgumentException("Parameter '{$name}' is not a valid integer.");
        }

        return (int) $this->values[$name];
    }

    /**
     * Returns all parameters as a raw associative array.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * Checks if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->values);
    }
}