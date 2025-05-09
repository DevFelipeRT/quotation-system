<?php

namespace App\Infrastructure\Logging;

use App\Infrastructure\Logging\Domain\LogEntry;

/**
 * Defines a contract for structured logging mechanisms.
 * Implementations may persist or transmit log entries
 * through various infrastructure strategies.
 */
interface LoggerInterface
{
    /**
     * Logs a structured log entry.
     *
     * @param LogEntry $entry The log entry to be recorded.
     */
    public function log(LogEntry $entry): void;
}
