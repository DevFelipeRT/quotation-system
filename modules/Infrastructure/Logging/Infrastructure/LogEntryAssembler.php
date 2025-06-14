<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Logging\Application\Contract\LogEntryAssemblerInterface;
use Logging\Domain\ValueObject\Contract\LogEntryInterface;
use Logging\Domain\ValueObject\LogEntry;
use Logging\Domain\ValueObject\LogLevel;
use Logging\Domain\ValueObject\LogMessage;
use Logging\Domain\ValueObject\LogContext;
use Logging\Domain\ValueObject\LogChannel;
use Logging\Domain\Security\Contract\LogSanitizerInterface;
use PublicContracts\Logging\LoggableInputInterface;
use Logging\Infrastructure\Exception\LogEntryAssemblyException;

/**
 * LogEntryAssembler
 *
 * Assembles immutable LogEntry objects from generic loggable input contracts.
 *
 * This class is responsible for converting raw input—typically received from
 * adapters, facades, or application boundaries—into validated and normalized
 * value objects for use throughout the logging domain.
 *
 * Responsibilities:
 *  - Orchestrates the creation of all relevant value objects (level, message, context, channel, timestamp).
 *  - Delegates validation and sanitation to the respective value object constructors.
 *  - Ensures only consistent, safe, and valid data enters the logging domain.
 *  - Captures and encapsulates any exception thrown during assembly in a
 *    LogEntryAssemblyException, preserving the cause for diagnostics.
 *
 * Usage:
 *  - Use as a bridge between input contracts and the domain model.
 *  - Inject the required LogSanitizerInterface to enforce consistent sanitization.
 */
final class LogEntryAssembler implements LogEntryAssemblerInterface
{
    private LogSanitizerInterface $sanitizer;

    /**
     * @param LogSanitizerInterface $sanitizer Sanitizer to apply during value object construction.
     */
    public function __construct(
        LogSanitizerInterface $sanitizer
    ) {
        $this->sanitizer = $sanitizer;
    }

    /**
     * Converts a generic loggable input into a fully validated LogEntry.
     *
     * @param LoggableInputInterface $input
     * @return LogEntryInterface
     *
     * @throws LogEntryAssemblyException If any part of the assembly fails.
     */
    public function assembleFromInput(LoggableInputInterface $input): LogEntryInterface
    {
        try {
            $level     = $this->buildLogLevel($input);
            $message   = $this->buildLogMessage($input);
            $context   = $this->buildLogContext($input);
            $channel   = $this->buildLogChannel($input);
            $timestamp = $input->getTimestamp();

            return new LogEntry(
                level: $level,
                message: $message,
                context: $context,
                channel: $channel,
                timestamp: $timestamp
            );
        } catch (\Throwable $e) {
            throw LogEntryAssemblyException::fromPrevious($input, $e);
        }
    }

    /**
     * Instantiates the LogLevel value object.
     */
    private function buildLogLevel(LoggableInputInterface $input): LogLevel
    {
        return new LogLevel($input->getCode() ?? 'info', $this->sanitizer);
    }

    /**
     * Instantiates the LogMessage value object.
     */
    private function buildLogMessage(LoggableInputInterface $input): LogMessage
    {
        return new LogMessage($input->getMessage(), $this->sanitizer);
    }

    /**
     * Instantiates the LogContext value object.
     */
    private function buildLogContext(LoggableInputInterface $input): LogContext
    {
        return new LogContext($input->getContext(), $this->sanitizer);
    }

    /**
     * Instantiates the LogChannel value object, or returns null if channel is absent.
     */
    private function buildLogChannel(LoggableInputInterface $input): ?LogChannel
    {
        $channel = $input->getChannel();
        return $channel === null ? null : new LogChannel($channel, $this->sanitizer);
    }
}
