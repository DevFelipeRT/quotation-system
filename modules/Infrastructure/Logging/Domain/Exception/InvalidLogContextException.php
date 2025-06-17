<?php

declare(strict_types=1);

namespace Logging\Domain\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when a log context is invalid.
 */
final class InvalidLogContextException extends InvalidArgumentException
{
    /**
     * Thrown when a context key is not a string.
     *
     * @param mixed $key
     * @return self
     */
    public static function invalidKeyType($key): self
    {
        return new self(
            'Log context keys must be strings. Invalid key: ' . var_export($key, true)
        );
    }

    /**
     * Thrown when a context key is empty or contains control characters.
     *
     * @param string $key
     * @return self
     */
    public static function invalidKeyContent(string $key): self
    {
        return new self(
            "Log context key '{$key}' is empty or contains control characters."
        );
    }

    /**
     * Thrown when a duplicate context key is detected.
     *
     * @param string $key
     * @return self
     */
    public static function duplicateKey(string $key): self
    {
        return new self(
            "Duplicate log context key detected: '{$key}'."
        );
    }

    /**
     * Thrown when a context value is not scalar or null.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public static function invalidValueType(string $key, $value): self
    {
        return new self(
            "Log context value for key '{$key}' must be scalar or null. Type: " . gettype($value)
        );
    }

    /**
     * Thrown when a context value is empty (except for 0 or false) or contains control characters.
     *
     * @param string $key
     * @return self
     */
    public static function invalidValueContent(string $key): self
    {
        return new self(
            "Log context value for key '{$key}' must be a non-empty string without control characters (except for 0 or false)."
        );
    }
}
