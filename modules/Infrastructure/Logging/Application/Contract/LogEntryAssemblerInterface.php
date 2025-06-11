<?php

declare(strict_types=1);

namespace Logging\Application;

use Logging\Domain\LogEntry;

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
     * @param LoggableInputInterface $input
     * @return LogEntry
     */
    public function assembleFromInput(LoggableInputInterface $input): LogEntry;
}
