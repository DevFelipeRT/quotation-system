<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Class RoutePath
 *
 * Represents a validated and immutable HTTP route path.
 */
final class RoutePath
{
    private readonly string $value;

    /**
     * @param string $path The HTTP route path.
     * @throws InvalidArgumentException If the path is invalid.
     */
    public function __construct(string $path)
    {
        $path = trim($path);

        if ($path === '' || $path[0] !== '/') {
            throw new InvalidArgumentException("Invalid route path: {$path}. Must start with '/'");
        }

        $this->value = $path;
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
}

