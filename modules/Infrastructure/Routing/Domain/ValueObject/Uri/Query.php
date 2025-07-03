<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Uri;

/**
 * Represents the query string component of a URI as an immutable value object.
 *
 * This object encapsulates the query string and provides methods to parse it
 * into an associative array of parameters.
 */
final class Query
{
    private readonly string $value;

    /**
     * @param string $value The raw query string, without the leading "?".
     * @throws \InvalidArgumentException If the query string is invalid.
     */
    public function __construct(string $value)
    {
        $this->ensureIsValid($value);

        $this->value = $value;
    }

    /**
     * Creates a Query object from an associative array of parameters.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $queryString = http_build_query($data, '', '&', PHP_QUERY_RFC3986);

        return new self($queryString);
    }

    /**
     * Returns the raw query string.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Parses the query string and returns its parameters as an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $params = [];
        if ($this->value !== '') {
            parse_str($this->value, $params);
        }
        return $params;
    }

    /**
     * Magic method to allow casting the object to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Checks if two Query objects are equal.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Ensures the provided query string is valid.
     *
     * @param string $value
     * @return void
     * @throws \InvalidArgumentException
     */
    private function ensureIsValid(string $value): void
    {
        if (str_contains($value, '#')) {
            throw new \InvalidArgumentException('Query string cannot contain a fragment identifier.');
        }
    }
}