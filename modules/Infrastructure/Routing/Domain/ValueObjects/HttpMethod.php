<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Class HttpMethod
 *
 * Represents a validated and immutable HTTP method.
 * Ensures that only standard and safe HTTP methods are accepted.
 */
final class HttpMethod
{
    /**
     * List of allowed HTTP methods.
     */
    private const ALLOWED_METHODS = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD',
    ];

    private readonly string $value;

    /**
     * HttpMethod constructor.
     *
     * @param string $value The HTTP method name (e.g., GET).
     * @throws InvalidArgumentException If the method is invalid or not supported.
     */
    public function __construct(string $value)
    {
        $normalized = $this->normalizeMethod($value);
        $this->validateMethod($normalized);
        $this->value = $normalized;
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

    /**
     * Normalizes the HTTP method (trims and uppercases).
     *
     * @param string $method
     * @return string
     */
    private function normalizeMethod(string $method): string
    {
        return strtoupper(trim($method));
    }

    /**
     * Validates the HTTP method for correctness and security.
     *
     * @param string $method
     * @throws InvalidArgumentException If the method is invalid or unsupported.
     */
    private function validateMethod(string $method): void
    {
        if ($method === '') {
            throw new InvalidArgumentException("HTTP method cannot be empty.");
        }

        if (!preg_match('/^[A-Z]+$/', $method)) {
            throw new InvalidArgumentException("HTTP method contains invalid characters: {$method}");
        }

        if (!in_array($method, self::ALLOWED_METHODS, true)) {
            throw new InvalidArgumentException("Invalid or unsupported HTTP method: {$method}");
        }
    }
}
