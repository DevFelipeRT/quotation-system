<?php

namespace App\Logging\Application;

use App\Logging\Domain\LogEntry;
use App\Logging\Domain\LogLevelEnum;
use App\Messaging\Domain\LoggableMessageInterface;
use InvalidArgumentException;

/**
 * Concrete implementation of LogEntryAssemblerInterface.
 *
 * Converts loggable messages into structured LogEntry objects
 * for persistence or monitoring purposes.
 */
final class LogEntryAssembler implements LogEntryAssemblerInterface
{
    /**
     * @inheritdoc
     */
    public function assembleFromMessage(LoggableMessageInterface $message): LogEntry
    {
        $level = $this->resolveLevelFromCode($message->getCode());

        return new LogEntry(
            level: $level,
            message: $message->getMessage(),
            context: $message->getContext(),
            channel: $message->getChannel(),
            timestamp: $message->getTimestamp()
        );
    }

    /**
     * Resolves the appropriate log level from a code string.
     *
     * @param string|null $code
     * @return LogLevelEnum
     */
    private function resolveLevelFromCode(?string $code): LogLevelEnum
    {
        return match (strtolower($code ?? 'info')) {
            'debug'     => LogLevelEnum::DEBUG,
            'info'      => LogLevelEnum::INFO,
            'warn',
            'warning'   => LogLevelEnum::WARNING,
            'error'     => LogLevelEnum::ERROR,
            'critical'  => LogLevelEnum::CRITICAL,
            default     => throw new InvalidArgumentException("Invalid log level code: {$code}"),
        };
    }
}
