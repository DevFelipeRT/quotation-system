<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Uri;

/**
 * Represents the scheme component of a URI as an immutable value object.
 *
 * This object validates and normalizes the URI scheme (e.g., "http", "https"),
 * ensuring it conforms to standard formats and is handled case-insensitively.
 */
final class Scheme
{
    private readonly string $value;

    /**
     * @param string $value The raw scheme string.
     * @throws \InvalidArgumentException If the scheme is invalid.
     */
    public function __construct(string $value)
    {
        $trimmedValue = trim($value);
        $this->ensureIsValid($trimmedValue);

        $this->value = strtolower($trimmedValue);
    }

    /**
     * Returns the normalized, lowercase scheme string.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Checks if the scheme is considered secure (e.g., "https", "wss").
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return in_array($this->value, ['https', 'wss'], true);
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
     * Checks if two Scheme objects are equal.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Ensures the provided scheme string is valid according to RFC 3986.
     *
     * @param string $value
     * @return void
     * @throws \InvalidArgumentException
     */
    private function ensureIsValid(string $value): void
    {
        if ($value === '') {
            throw new \InvalidArgumentException('Scheme cannot be empty.');
        }

        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*$/', $value)) {
            throw new \InvalidArgumentException("Invalid scheme format: \"{$value}\".");
        }
    }
}