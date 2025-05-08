<?php

namespace App\Infrastructure\Messaging\Domain;

use DateTimeImmutable;

/**
 * Contract for structured, immutable application messages.
 *
 * Defines the minimal behavior required for all message types
 * across the system, including log messages, user-facing messages,
 * notifications, and audit entries.
 *
 * This interface does not define formatting, transport, or rendering behavior.
 */
interface MessageInterface
{
    /**
     * Returns the main human-readable content of the message.
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Returns contextual data associated with the message.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array;

    /**
     * Returns an optional classification or severity code.
     *
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * Returns the timestamp of when the message was created.
     *
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable;
}
