<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Logging\Application\Contract\LogEntryAssemblerInterface;
use Logging\Domain\Contract\LogEntryInterface;
use Logging\Domain\ValueObject\LogEntry;
use Logging\Domain\ValueObject\LogLevelEnum;
use Logging\Exception\InvalidLogLevelException;
use PublicContracts\Logging\LoggableInputInterface;

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

    public function assembleFromInput(LoggableInputInterface $input): LogEntryInterface
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
