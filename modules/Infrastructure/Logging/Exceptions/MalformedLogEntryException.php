<?php

declare(strict_types=1);

namespace Logging\Exceptions;

/**
 * Thrown when a LogEntry instance contains invalid or missing data.
 *
 * This helps catch structural issues before writing to a log sink.
 */
final class MalformedLogEntryException extends LoggingException
{
}
