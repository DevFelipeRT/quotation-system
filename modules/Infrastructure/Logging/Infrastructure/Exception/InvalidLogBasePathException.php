<?php

declare(strict_types=1);

namespace Logging\Infrastructure\Exception;

use InvalidArgumentException;

/**
 * Thrown when the base path for log files is invalid (empty, malformed, or not a directory).
 */
final class InvalidLogBasePathException extends InvalidArgumentException
{
    /**
     * Creates an exception for empty base path.
     */
    public static function empty(): self
    {
        return new self('Base path for log files cannot be empty.');
    }

    /**
     * Creates an exception for invalid characters.
     */
    public static function invalidCharacters(): self
    {
        return new self('Base path for log files contains invalid control characters.');
    }

    /**
     * Creates an exception for a path that looks like a file.
     */
    public static function notADirectory(): self
    {
        return new self('Base path for log files must be a directory, not a file.');
    }
}
