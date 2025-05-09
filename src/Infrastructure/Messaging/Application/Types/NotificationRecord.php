<?php

namespace App\Infrastructure\Messaging\Application\Types;

use App\Infrastructure\Messaging\Application\AbstractMessage;
use DateTimeImmutable;

/**
 * Represents a structured notification message to inform or alert users.
 *
 * Suitable for domain-level alerts, system announcements, or user-facing
 * notifications that may be dispatched via UI, email, SMS, or push services.
 */
final class NotificationMessage extends AbstractMessage
{
    /**
     * Returns the message type identifier.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'notification';
    }

    /**
     * Creates a new notification message instance.
     *
     * @param string $text Message content to be delivered.
     * @param array<string, mixed> $context Additional metadata (optional).
     * @param string|null $code Optional category code (e.g. 'ORDER_CONFIRMED').
     * @param DateTimeImmutable|null $timestamp Optional timestamp (default: now).
     * @return self
     */
    public static function create(
        string $text,
        array $context = [],
        ?string $code = null,
        ?DateTimeImmutable $timestamp = null
    ): self {
        return new self($text, $context, $code, $timestamp);
    }
}
