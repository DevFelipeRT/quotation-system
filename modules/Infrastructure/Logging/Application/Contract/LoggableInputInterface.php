<?php

declare(strict_types=1);

namespace Logging\Application;

use DateTimeImmutable;

/**
 * Defines a contract for loggable structures accepted by the logging module.
 */
interface LoggableInputInterface
{
    /**
     * Returns the log message content.
     */
    public function getMessage(): string;

    /**
     * Returns context data for the log entry.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array;

    /**
     * Returns the optional log level code (e.g., 'info', 'error').
     */
    public function getCode(): ?string;

    /**
     * Returns the logical channel for categorizing the log.
     */
    public function getChannel(): ?string;

    /**
     * Returns the timestamp when the log was created.
     */
    public function getTimestamp(): DateTimeImmutable;
}
