<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use DateTimeImmutable;
use Logging\Domain\ValueObject\Contract\LogEntryInterface;

/**
 * Immutable Value Object representing a structured log entry.
 *
 * Delegates validation and sanitization responsibilities entirely
 * to its constituent Value Objects, ensuring domain consistency and security.
 * 
 * @immutable
 */
final class LogEntry implements LogEntryInterface
{
    private LogLevel $level;
    private LogMessage $message;
    private LogContext $context;
    private LogChannel $channel;
    private DateTimeImmutable $timestamp;

    /**
     * Constructs a LogEntry instance from validated Value Objects.
     *
     * @param LogLevel               $level     Severity level.
     * @param LogMessage             $message   Log message.
     * @param LogContext             $context   Contextual data.
     * @param LogChannel             $channel   Categorization channel.
     * @param DateTimeImmutable|null $timestamp Creation timestamp (defaults to current time).
     */
    public function __construct(
        LogLevel $level,
        LogMessage $message,
        LogContext $context,
        LogChannel $channel,
        ?DateTimeImmutable $timestamp = null
    ) {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
        $this->channel = $channel;
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    /**
     * Retrieves the log level.
     *
     * @return LogLevel
     */
    public function getLevel(): LogLevel
    {
        return $this->level;
    }

    /**
     * Retrieves the log message.
     *
     * @return LogMessage
     */
    public function getMessage(): LogMessage
    {
        return $this->message;
    }

    /**
     * Retrieves the log context.
     *
     * @return LogContext|null
     */
    public function getContext(): LogContext
    {
        return $this->context;
    }

    /**
     * Retrieves the log channel, if defined.
     *
     * @return LogChannel|null
     */
    public function getChannel(): ?LogChannel
    {
        return $this->channel;
    }

    /**
     * Retrieves the timestamp of the log entry creation.
     *
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Prevents PHP serialization for enhanced security.
     *
     * @throws \LogicException Always thrown to disallow serialization.
     */
    public function __serialize(): array
    {
        throw new \LogicException('Serialization of LogEntry objects is not permitted for security reasons.');
    }

    /**
     * Prevents PHP unserialization for enhanced security.
     *
     * @throws \LogicException Always thrown to disallow unserialization.
     */
    public function __unserialize(array $data): void
    {
        throw new \LogicException('Unserialization of LogEntry objects is not permitted for security reasons.');
    }
}
