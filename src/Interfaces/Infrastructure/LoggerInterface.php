<?php

namespace App\Interfaces\Infrastructure;

use App\Infrastructure\Logging\LogEntry;

/**
 * Interface LoggerInterface
 *
 * Defines a contract for structured logging mechanisms.
 * Allows any implementing class to persist or transmit log entries
 * according to infrastructure-specific strategies.
 */
interface LoggerInterface
{
    /**
     * Logs a structured log entry.
     *
     * @param LogEntry $entry The log entry to be recorded.
     * @return void
     */
    public function log(LogEntry $entry): void;
}
