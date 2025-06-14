<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use DateTimeImmutable;
use Logging\Domain\ValueObject\Contract\LogEntryInterface;

/**
 * Represents a structured, immutable log entry.
 *
 * All validation and sanitization must be handled by the respective Value Objects.
 */
final class LogEntry implements LogEntryInterface
{
    private LogLevel $level;
    private LogMessage $message;
    private ?LogContext $context;
    private ?LogChannel $channel;
    private DateTimeImmutable $timestamp;

    /**
     * @param LogLevel                  $level     Log severity level.
     * @param LogMessage                $message   Log message (VO).
     * @param LogContext|null           $context   Optional contextual information.
     * @param LogChannel|null           $channel   Optional channel categorization.
     * @param DateTimeImmutable|null    $timestamp Time of log creation (defaults to now).
     */
    public function __construct(
        LogLevel $level,
        LogMessage $message,
        ?LogContext $context = null,
        ?LogChannel $channel = null,
        ?DateTimeImmutable $timestamp = null
    ) {
        $this->level     = $level;
        $this->message   = $message;
        $this->context   = $context;
        $this->channel   = $channel;
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    public function getLevel(): LogLevel
    {
        return $this->level;
    }

    public function getMessage(): LogMessage
    {
        return $this->message;
    }

    public function getContext(): LogContext
    {
        return $this->context;
    }

    public function getChannel(): ?LogChannel
    {
        return $this->channel;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Block PHP serialization/deserialization for extra safety.
     */
    public function __serialize(): array
    {
        throw new \LogicException('Serialization of LogEntry objects is not permitted for security reasons.');
    }

    public function __unserialize(array $data): void
    {
        throw new \LogicException('Unserialization of LogEntry objects is not permitted for security reasons.');
    }
}
