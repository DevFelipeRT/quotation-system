<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Class RoutePath
 *
 * Represents a validated and immutable HTTP route path.
 * Ensures security and consistency by enforcing strict validation rules.
 */
final class RoutePath
{
    private readonly string $value;

    /**
     * Maximum allowed length for a route path.
     */
    private const MAX_LENGTH = 2048;

    /**
     * RoutePath constructor.
     *
     * @param string $path The HTTP route path.
     * @throws InvalidArgumentException If the path is invalid or unsafe.
     */
    public function __construct(string $path)
    {
        $path = trim($path);

        $this->validatePath($path);

        $this->value = $this->normalizePath($path);
    }

    /**
     * Creates an instance from string.
     *
     * @param string $path
     * @return static
     */
    public static function fromString(string $path): self
    {
        return new self($path);
    }

    /**
     * Returns the path as string.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Checks if this path is equal to another.
     *
     * @param RoutePath $other
     * @return bool
     */
    public function equals(RoutePath $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Returns the string representation of the path.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    private function validatePath(string $path): void
    {
        if ($path === '' || $path[0] !== '/') {
            throw new InvalidArgumentException("Invalid route path: must start with '/' and not be empty.");
        }

        if (strlen($path) > self::MAX_LENGTH) {
            throw new InvalidArgumentException("Route path too long (max " . self::MAX_LENGTH . " characters).");
        }

        if (strpos($path, '..') !== false) {
            throw new InvalidArgumentException("Route path must not contain '..' (path traversal is not allowed).");
        }

        if (strpos($path, '//') !== false) {
            throw new InvalidArgumentException("Route path must not contain consecutive slashes ('//').");
        }

        if (strpos($path, '?') !== false) {
            throw new InvalidArgumentException("Route path must not contain query strings ('?').");
        }
    }

    private function normalizePath(string $path): string
    {
        // Remove trailing slashes except for root path
        if ($path !== '/' && str_ends_with($path, '/')) {
            return rtrim($path, '/');
        }
        return $path;
    }
}
