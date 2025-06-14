<?php

namespace Logging\Domain\ValueObject\Contract;

use DateTimeImmutable;
use Logging\Domain\ValueObject\LogLevel;
use Logging\Domain\ValueObject\LogMessage;
use Logging\Domain\ValueObject\LogContext;
use Logging\Domain\ValueObject\LogChannel;

/**
 * Contract for immutable, structured log entries.
 *
 * Security Notes:
 *  - Implementations MUST ensure that sensitive context data is masked or filtered.
 *  - The log entry must be strictly immutable after construction.
 *  - No mutators (setters) are permitted.
 */
interface LogEntryInterface
{
    /**
     * Returns the log level (severity) of the entry.
     *
     * @return LogLevel
     */
    public function getLevel(): LogLevel;

    /**
     * Returns the main log message.
     *
     * Implementations MUST ensure the message is safe for logging
     * and does not contain sensitive or untrusted data.
     *
     * @return LogMessage
     */
    public function getMessage(): LogMessage;

    /**
     * Returns the context for the log entry.
     *
     * The context SHOULD NOT expose passwords, tokens, PII, or other
     * sensitive data. Implementations must sanitize context if needed.
     *
     * @return LogContext
     */
    public function getContext(): LogContext;

    /**
     * Returns the optional channel or category for the log.
     *
     * @return LogChannel|null
     */
    public function getChannel(): ?LogChannel;

    /**
     * Returns the timestamp when the log entry was created.
     *
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable;
}
