<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Definition;

/**
 * Represents a URI path pattern for route definitions.
 *
 * This value object encapsulates a path string that may contain placeholders
 * (e.g., "/users/{id}") and provides methods to work with that pattern,
 * such as extracting placeholder names.
 */
final class PathPattern
{
    private readonly string $value;

    /**
     * @param string $value The raw path pattern string.
     * @throws \InvalidArgumentException if the pattern is invalid.
     */
    public function __construct(string $value)
    {
        $this->ensureIsValid($value);

        $this->value = $this->normalize($value);
    }

    /**
     * Returns the normalized string representation of the pattern.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Extracts the names of the placeholders from the pattern.
     *
     * For a pattern like "/users/{userId}/posts/{postId}", this method
     * would return ['userId', 'postId'].
     *
     * @return string[]
     */
    public function getPlaceholderNames(): array
    {
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_-]*)\}/', $this->value, $matches);

        return $matches[1] ?? [];
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
     * Checks if two PathPattern objects are equal.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Ensures the provided pattern string is valid.
     *
     * @param string $value
     * @return void
     * @throws \InvalidArgumentException
     */
    private function ensureIsValid(string $value): void
    {
        if (str_contains($value, '?') || str_contains($value, '#')) {
            throw new \InvalidArgumentException('Path pattern cannot contain a query string or fragment.');
        }

        if ($value === '') {
            throw new \InvalidArgumentException('Path pattern cannot be empty.');
        }
    }

    /**
     * Normalizes the path pattern string.
     *
     * @param string $value
     * @return string
     */
    private function normalize(string $value): string
    {
        if ($value[0] !== '/') {
            $value = '/' . $value;
        }

        if (strlen($value) > 1 && substr($value, -1) === '/') {
            $value = rtrim($value, '/');
        }

        return $value;
    }
}