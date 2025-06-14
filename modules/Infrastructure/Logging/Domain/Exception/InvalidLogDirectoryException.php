<?php

declare(strict_types=1);

namespace Logging\Domain\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when a log directory path is invalid or unsafe.
 */
final class InvalidLogDirectoryException extends InvalidArgumentException
{
    public static function empty(): self
    {
        return new self('Log directory path must not be empty.');
    }

    public static function unsafe(string $reason): self
    {
        return new self("Log directory path is unsafe: {$reason}");
    }

    public static function notWritable(string $path): self
    {
        return new self("Log directory path is not writable: {$path}");
    }
}
