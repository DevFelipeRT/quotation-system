<?php

declare(strict_types=1);

namespace Logging\Application\Contract;

use Logging\Domain\ValueObject\Contract\LogEntryInterface;

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
     * @param LogEntryInterface $entry The log entry to be recorded.
     */
    public function log(LogEntryInterface $entry): void;
}
