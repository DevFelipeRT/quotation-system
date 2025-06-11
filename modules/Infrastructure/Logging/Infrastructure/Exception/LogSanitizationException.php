<?php

declare(strict_types=1);

namespace Logging\Exception;

use Logging\Exception\Contract\LoggingException;

/**
 * Thrown when a log entry contains data that cannot be sanitized properly.
 *
 * This is used to prevent logging of unsafe or malformed sensitive content.
 */
final class LogSanitizationException extends LoggingException
{
}
