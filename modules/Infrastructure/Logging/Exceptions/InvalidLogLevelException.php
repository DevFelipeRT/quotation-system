<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging\Exceptions;

/**
 * Thrown when a provided log level is invalid or not recognized.
 *
 * This exception is typically triggered by LogLevelEnum::fromPsrLevel()
 * when a non-PSR-3-compliant level string is passed.
 */
final class InvalidLogLevelException extends LoggingException
{
}
