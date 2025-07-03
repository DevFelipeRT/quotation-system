<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Uri;

/**
 * Represents the path component of a URI as a typesafe value object.
 *
 * This object ensures that a URI path is always in a valid and
 * consistent state. It is a fundamental building block for representing
 * a concrete request path.
 */
final class Path
{
    private readonly string $value;

    /**
     * @param string $value The raw path string.
     * @throws \InvalidArgumentException if the path is invalid.
     */
    public function __construct(string $value)
    {
        $this->ensureIsValid($value);

        $this->value = $this->normalize($value);
    }

    /**
     * Returns the normalized string representation of the path.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
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
     * Checks if two Path objects are equal.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Ensures the provided path string is valid.
     *
     * @param string $value
     * @return void
     * @throws \InvalidArgumentException
     */
    private function ensureIsValid(string $value): void
    {
        if (mb_strpos($value, '#') !== false || mb_strpos($value, '?') !== false) {
            throw new \InvalidArgumentException('Path cannot contain a query string or fragment.');
        }
    }

    /**
     * Normalizes the path string.
     *
     * Ensures the path starts with a "/" and does not have a trailing slash (unless it is the root path).
     *
     * @param string $value
     * @return string
     */
    private function normalize(string $value): string
    {
        if (empty($value) || $value[0] !== '/') {
            $value = '/' . $value;
        }

        if (strlen($value) > 1 && substr($value, -1) === '/') {
            $value = rtrim($value, '/');
        }

        return $value;
    }
}