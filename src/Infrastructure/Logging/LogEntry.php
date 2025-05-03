<?php

namespace App\Infrastructure\Logging;

use DateTimeImmutable;

/**
 * Class LogEntry
 *
 * Represents a structured, immutable log entry,
 * suitable for persistent storage or transmission (e.g., files, databases, queues).
 */
final class LogEntry
{
    private LogLevelEnum $level;
    private string $message;
    private array $context;
    private ?string $channel;
    private DateTimeImmutable $timestamp;

    /**
     * LogEntry constructor.
     *
     * @param LogLevelEnum $level     Severity level of the log entry.
     * @param string        $message   Log message content.
     * @param array         $context   Optional contextual metadata (e.g., exception data, request info).
     * @param string|null   $channel   Optional log channel for categorization (e.g., auth, payment).
     * @param DateTimeImmutable|null $timestamp Timestamp of log creation (defaults to now).
     */
    public function __construct(
        LogLevelEnum $level,
        string $message,
        array $context = [],
        ?string $channel = null,
        ?DateTimeImmutable $timestamp = null
    ) {
        $this->level     = $level;
        $this->message   = $message;
        $this->context   = $context;
        $this->channel   = $channel;
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    public function getLevel(): LogLevelEnum
    {
        return $this->level;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
