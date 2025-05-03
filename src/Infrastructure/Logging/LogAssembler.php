<?php

namespace App\Infrastructure\Logging;

use App\Application\Messaging\LogMessage;
use InvalidArgumentException;

/**
 * Assembles a LogEntry from a LogMessage.
 *
 * Acts as a boundary translator between domain/application messages
 * and technical logging structures.
 */
final class LogAssembler
{
    /**
     * Converts a LogMessage into a LogEntry.
     *
     * @param LogMessage $message
     * @return LogEntry
     */
    public function fromLogMessage(LogMessage $message): LogEntry
    {
        $level = $this->mapCodeToLevel($message->getCode());

        return new LogEntry(
            level: $level,
            message: $message->getMessage(),
            context: $message->getContext(),
            channel: null,
            timestamp: $message->getTimestamp()
        );
    }

    /**
     * Maps a message code to a LogLevelEnum.
     *
     * @param string|null $code
     * @return LogLevelEnum
     */
    private function mapCodeToLevel(?string $code): LogLevelEnum
    {
        return match (strtolower($code ?? 'info')) {
            'debug'     => LogLevelEnum::DEBUG,
            'info'      => LogLevelEnum::INFO,
            'warn', 
            'warning'   => LogLevelEnum::WARNING,
            'error'     => LogLevelEnum::ERROR,
            'critical'  => LogLevelEnum::CRITICAL,
            default     => throw new InvalidArgumentException("Invalid log code: {$code}"),
        };
    }
}
