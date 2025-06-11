<?php

declare(strict_types=1);

namespace Logging\Exception;

use Logging\Exception\Contract\LoggingException;

/**
 * Raised when a log file cannot be written to.
 */
class LogWriteException extends LoggingException
{
    /**
     * Creates a standardized exception for file write failure.
     *
     * @param string $path
     * @return static
     */
    public static function cannotWrite(string $path): self
    {
        return new self("Unable to write log entry to file: {$path}");
    }
}
