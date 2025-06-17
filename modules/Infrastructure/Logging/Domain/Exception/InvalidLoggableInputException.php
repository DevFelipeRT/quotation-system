<?php

declare(strict_types=1);

namespace Logging\Domain\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when a LoggableInput is invalid.
 */
final class InvalidLoggableInputException extends InvalidArgumentException
{
    public static function emptyLevel(): self
    {
        return new self('Log level code must not be empty.');
    }

    public static function emptyMessage(): self
    {
        return new self('Log message must not be empty.');
    }

    public static function invalidContextKey($key): self
    {
        return new self("Log context key must be a non-empty string. Invalid key: " . var_export($key, true));
    }

    public static function invalidContextValue($key): self
    {
        return new self("Log context value for key '{$key}' must be a string.");
    }

    public static function emptyChannel(): self
    {
        return new self('Log channel must not be empty.');
    }
}
