<?php

namespace App\Logging\Application;

use App\Logging\Domain\LogEntry;
use App\Messaging\Domain\LoggableMessageInterface;

/**
 * Defines the contract for assembling LogEntry objects from loggable messages.
 */
interface LogEntryAssemblerInterface
{
    /**
     * Assembles a LogEntry instance from a given loggable message.
     *
     * @param LoggableMessageInterface $message
     * @return LogEntry
     */
    public function assembleFromMessage(LoggableMessageInterface $message): LogEntry;
}
