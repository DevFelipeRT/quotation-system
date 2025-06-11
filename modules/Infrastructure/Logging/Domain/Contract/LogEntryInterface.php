<?php

namespace Logging\Domain\Contract;

use DateTimeImmutable;
use Logging\Domain\ValueObject\LogLevelEnum;

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
     * @return LogLevelEnum
     */
    public function getLevel(): LogLevelEnum;

    /**
     * Returns the main log message.
     *
     * Implementations MUST ensure the message is safe for logging
     * and does not contain sensitive or untrusted data.
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Returns the context array for the log entry.
     *
     * The context SHOULD NOT expose passwords, tokens, PII, or other
     * sensitive data. Implementations must sanitize context if needed.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array;

    /**
     * Returns the optional channel or category for the log.
     *
     * @return string|null
     */
    public function getChannel(): ?string;

    /**
     * Returns the timestamp when the log entry was created.
     *
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable;
}
