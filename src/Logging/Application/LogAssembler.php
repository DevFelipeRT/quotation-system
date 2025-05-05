<?php

namespace App\Logging\Application;

use App\Logging\Domain\LogEntry;
use App\Logging\Domain\LogLevelEnum;
use App\Messaging\Domain\LoggableMessageInterface;
use InvalidArgumentException;

/**
 * Assembles a LogEntry from a loggable message.
 *
 * Translates a LogMessage into a persistence-friendly structure
 * to be consumed by log writers, audit trails or monitoring tools.
 */
final class LogAssembler
{
    /**
     * Converts a loggable message into a LogEntry.
     *
     * @param LoggableMessageInterface $message
     * @return LogEntry
     */
    public function fromLogMessage(LoggableMessageInterface $message): LogEntry
    {
        $level = $this->mapCodeToLevel($message->getCode());

        return new LogEntry(
            level: $level,
            message: $message->getMessage(),
            context: $message->getContext(),
            channel: $message->getChannel(),
            timestamp: $message->getTimestamp()
        );
    }

    /**
     * Maps a log code string to a LogLevelEnum instance.
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
