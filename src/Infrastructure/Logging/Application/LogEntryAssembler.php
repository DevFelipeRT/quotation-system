<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging\Application;

use App\Infrastructure\Logging\Domain\LogEntry;
use App\Infrastructure\Logging\Domain\LogLevelEnum;
use App\Infrastructure\Logging\Exceptions\InvalidLogLevelException;
use App\Infrastructure\Logging\Security\LogSanitizer;

/**
 * Converts loggable inputs into structured LogEntry objects.
 *
 * Applies security sanitization to context data and ensures level mapping.
 */
final class LogEntryAssembler implements LogEntryAssemblerInterface
{
    public function __construct(
        private readonly LogSanitizer $sanitizer
    ) {}

    public function assembleFromMessage(LoggableInputInterface $message): LogEntry
    {
        $level = $this->resolveLevelFromCode($message->getCode());
        $sanitizedContext = $this->sanitizer->sanitize($message->getContext());

        return new LogEntry(
            level: $level,
            message: $message->getMessage(),
            context: $sanitizedContext,
            channel: $message->getChannel(),
            timestamp: $message->getTimestamp()
        );
    }

    /**
     * Resolves a log level from an optional string code.
     *
     * @param string|null $code
     * @return LogLevelEnum
     *
     * @throws InvalidLogLevelException If the code is not recognized
     */
    private function resolveLevelFromCode(?string $code): LogLevelEnum
    {
        return match (strtolower($code ?? 'info')) {
            'debug'     => LogLevelEnum::DEBUG,
            'info'      => LogLevelEnum::INFO,
            'warn', 'warning' => LogLevelEnum::WARNING,
            'error'     => LogLevelEnum::ERROR,
            'critical'  => LogLevelEnum::CRITICAL,
            default     => throw new InvalidLogLevelException("Invalid log level code: {$code}"),
        };
    }
}
