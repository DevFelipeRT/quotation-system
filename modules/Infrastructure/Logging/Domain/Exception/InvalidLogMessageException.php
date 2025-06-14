<?php

declare(strict_types=1);

namespace Logging\Domain\Exception;

use InvalidArgumentException;

/**
 * Thrown when a log message is invalid (empty, contains control characters, or is too long).
 */
final class InvalidLogMessageException extends InvalidArgumentException
{
    public static function empty(): self
    {
        return new self('Log message must not be empty.');
    }

    public static function invalidCharacters(): self
    {
        return new self('Log message contains invalid control characters.');
    }

    public static function tooLong(): self
    {
        return new self('Log message is too long (limit: 2000 characters).');
    }
}
