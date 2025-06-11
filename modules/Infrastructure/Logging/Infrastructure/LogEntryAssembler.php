<?php

declare(strict_types=1);

namespace Logging\Application;

use Logging\Domain\LogEntry;
use Logging\Domain\LogLevelEnum;
use Logging\Exceptions\InvalidLogLevelException;
use Logging\Security\LogSanitizer;

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

    public function assembleFromInput(LoggableInputInterface $input): LogEntry
    {
        $level = $this->resolveLevelFromCode($input->getCode());
        $sanitizedContext = $this->sanitizer->sanitize($input->getContext());

        return new LogEntry(
            level: $level,
            message: $input->getMessage(),
            context: $sanitizedContext,
            channel: $input->getChannel(),
            timestamp: $input->getTimestamp()
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
