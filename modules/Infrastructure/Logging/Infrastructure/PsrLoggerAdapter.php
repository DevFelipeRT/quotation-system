<?php

declare(strict_types=1);

namespace Logging\Infrastructure\Adapter;

use Logging\Domain\LogEntry;
use Logging\Domain\LogLevelEnum;
use Logging\Infrastructure\Contracts\LoggerInterface;
use Logging\Infrastructure\Contracts\PsrLoggerInterface;
use Stringable;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Adapter for structured logging that implements a PSR-3 compatible interface.
 *
 * This class receives PSR-style log calls and delegates the logging
 * process to a domain-driven LoggerInterface, translating log levels
 * and encapsulating messages as structured LogEntry instances.
 */
final class PsrLoggerAdapter implements PsrLoggerInterface
{
    public function __construct(
        private readonly LoggerInterface $structuredLogger
    ) {}

    /**
     * Logs a message with the given PSR-3 level.
     *
     * @param string $level The PSR-3 log level (e.g., 'error', 'info')
     * @param string|Stringable $message The message to log
     * @param array<string, mixed> $context Optional context for interpolation and metadata
     *
     * @throws InvalidArgumentException If the message is not a string or Stringable
     */
    public function log(string $level, string|Stringable $message, array $context = []): void
    {
        $this->validateMessage($message);

        $enumLevel = $this->resolveLogLevel($level);

        $entry = $this->createLogEntry($enumLevel, (string) $message, $context);

        $this->structuredLogger->log($entry);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Validates the type of the log message.
     *
     * @param string|Stringable $message
     * @throws InvalidArgumentException If the message is not a valid type
     */
    private function validateMessage(string|Stringable $message): void
    {
        if (!is_string($message) && !$message instanceof Stringable) {
            throw new InvalidArgumentException('Log message must be a string or implement Stringable.');
        }
    }

    /**
     * Converts a PSR-3 log level string to a LogLevelEnum.
     *
     * @param string $level
     * @return LogLevelEnum
     *
     * @throws InvalidArgumentException If the level is unsupported
     */
    private function resolveLogLevel(string $level): LogLevelEnum
    {
        return LogLevelEnum::fromPsrLevel($level);
    }

    /**
     * Creates a structured LogEntry.
     *
     * @param LogLevelEnum $level
     * @param string $message
     * @param array<string, mixed> $context
     * @return LogEntry
     */
    private function createLogEntry(LogLevelEnum $level, string $message, array $context): LogEntry
    {
        return new LogEntry(
            level: $level,
            message: $message,
            context: $context,
            timestamp: new DateTimeImmutable()
        );
    }
}
