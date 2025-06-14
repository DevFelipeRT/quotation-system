<?php

declare(strict_types=1);

namespace Logging\Domain\Exception;

use InvalidArgumentException;

/**
 * Thrown when a provided log level is invalid or not recognized.
 *
 * This exception is typically triggered by LogLevelEnum::fromPsrLevel()
 * when a non-PSR-3-compliant level string is passed.
 */
final class InvalidLogLevelException extends InvalidArgumentException
{
    /**
     * Creates an exception for an invalid log level.
     *
     * @param string|null $level
     * @return self
     */
    public static function forLevel(?string $level): self
    {
        $display = $level === null ? '[null]' : $level;
        return new self("Invalid or unrecognized log level: {$display}");
    }
}
