<?php

declare(strict_types=1);

namespace Logging\Domain\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when a log context is invalid.
 */
final class InvalidLogContextException extends InvalidArgumentException
{
    public static function invalidKeyType($key): self
    {
        return new self('Log context keys must be strings. Invalid key: ' . var_export($key, true));
    }

    public static function invalidKeyContent(string $key): self
    {
        return new self("Log context key '{$key}' is empty or contains invalid characters.");
    }

    public static function duplicateKey(string $key): self
    {
        return new self("Duplicate log context key detected: '{$key}'.");
    }

    public static function invalidValueType(string $key, $value): self
    {
        return new self("Log context value for key '{$key}' must be scalar, array or null after sanitization. Type: " . gettype($value));
    }
}
