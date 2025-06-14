<?php

declare(strict_types=1);

namespace Logging\Domain\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when a log channel is invalid.
 */
final class InvalidLogChannelException extends InvalidArgumentException
{
    public static function empty(): self
    {
        return new self('Log channel must not be empty.');
    }

    public static function invalidCharacters(): self
    {
        return new self('Log channel contains invalid control characters.');
    }

    public static function tooLong(): self
    {
        return new self('Log channel name is too long (limit: 128 characters).');
    }
}
