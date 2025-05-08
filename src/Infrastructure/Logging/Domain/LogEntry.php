<?php

namespace App\Infrastructure\Logging\Domain;

use DateTimeImmutable;

/**
 * Represents a structured, immutable log entry.
 *
 * This Value Object is used to encapsulate all necessary information
 * for recording log events, including severity, message, contextual data,
 * category channel, and timestamp.
 */
final class LogEntry
{
    private LogLevelEnum $level;
    private string $message;
    private array $context;
    private ?string $channel;
    private DateTimeImmutable $timestamp;

    /**
     * @param LogLevelEnum             $level     Log severity level.
     * @param string                   $message   Log message text.
     * @param array                    $context   Optional contextual information.
     * @param string|null              $channel   Optional channel categorization (e.g., 'auth', 'payment').
     * @param DateTimeImmutable|null   $timestamp Time of log creation (defaults to now).
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
