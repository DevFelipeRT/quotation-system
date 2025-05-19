<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Class HttpMethod
 *
 * Represents a validated, immutable HTTP method.
 */
final class HttpMethod
{
    private const ALLOWED_METHODS = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD',
    ];

    private readonly string $value;

    /**
     * @param string $value The HTTP method name (e.g. GET).
     * @throws InvalidArgumentException If method is not allowed.
     */
    public function __construct(string $value)
    {
        $upper = strtoupper($value);
        if (!in_array($upper, self::ALLOWED_METHODS, true)) {
            throw new InvalidArgumentException("Invalid HTTP method: {$value}");
        }

        $this->value = $upper;
    }

    /**
     * Creates an instance from string.
     *
     * @param string $method
     * @return static
     */
    public static function fromString(string $method): self
    {
        return new self($method);
    }

    /**
     * Gets the HTTP method as string.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Compares with another HttpMethod.
     *
     * @param HttpMethod $other
     * @return bool
     */
    public function equals(HttpMethod $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Returns method as string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
