<?php

namespace App\Application\Messaging;

use DateTimeImmutable;

/**
 * Contract for immutable, structured system messages.
 */
interface MessageInterface
{
    /**
     * Returns the message type identifier (e.g., "log", "audit", "notification").
     */
    public function getType(): string;

    /**
     * Returns the main human-readable message text.
     */
    public function getMessage(): string;

    /**
     * Returns contextual metadata associated with the message.
     */
    public function getContext(): array;

    /**
     * Returns an optional categorization code for the message (e.g., severity, status).
     */
    public function getCode(): ?string;

    /**
     * Returns the creation timestamp as a DateTimeImmutable object.
     */
    public function getTimestamp(): DateTimeImmutable;

    /**
     * Returns the creation timestamp formatted as a string (e.g., for serialization).
     */
    public function formattedTimestamp(): string;

    /**
     * Serializes the message into a standard associative array.
     */
    public function toArray(): array;
}
