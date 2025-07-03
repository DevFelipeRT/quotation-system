<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Uri;

/**
 * Represents the host component of a URI as an immutable value object.
 *
 * Ensures the host is a valid domain name or IP address and normalizes it
 * to lowercase for case-insensitive comparison.
 */
final class Host
{
    private readonly string $value;

    /**
     * @param string $value The raw host string (e.g., "example.com", "127.0.0.1").
     * @throws \InvalidArgumentException If the host is invalid.
     */
    public function __construct(string $value)
    {
        $trimmedValue = trim($value);
        $this->ensureIsValid($trimmedValue);

        $this->value = strtolower($trimmedValue);
    }

    /**
     * Returns the normalized, lowercase host string.
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
     * Checks if two Host objects are equal.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Checks if the host is a valid IPv4 address.
     * @return bool
     */
    public function isIpV4(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Checks if the host is a valid IPv6 address.
     * @return bool
     */
    public function isIpV6(): bool
    {
        // Note: IPv6 literals in URIs are enclosed in square brackets.
        // The value stored here does not contain the brackets.
        return filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Validates the host string.
     *
     * @param string $value
     * @throws \InvalidArgumentException
     */
    private function ensureIsValid(string $value): void
    {
        if ($value === '') {
            throw new \InvalidArgumentException('Host cannot be empty.');
        }

        // Check for valid IP formats first.
        if (filter_var($value, FILTER_VALIDATE_IP)) {
            return;
        }

        // For domain names, use a combination of filter_var and a fallback for localhost.
        // FILTER_FLAG_HOSTNAME is added to support internationalized domain names (IDN).
        if (filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) || $value === 'localhost') {
            return;
        }

        throw new \InvalidArgumentException("Invalid host format: \"{$value}\".");
    }
}