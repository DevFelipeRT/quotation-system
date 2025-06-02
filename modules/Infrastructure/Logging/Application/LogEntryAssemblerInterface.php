<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging\Application;

use App\Infrastructure\Logging\Domain\LogEntry;

/**
 * Defines the contract for assembling LogEntry objects from loggable inputs.
 *
 * This interface ensures that any input satisfying LoggableInputInterface
 * can be converted into a structured LogEntry for logging purposes.
 */
interface LogEntryAssemblerInterface
{
    /**
     * Assembles a LogEntry instance from a given loggable input.
     *
     * @param LoggableInputInterface $message
     * @return LogEntry
     */
    public function assembleFromMessage(LoggableInputInterface $message): LogEntry;
}
