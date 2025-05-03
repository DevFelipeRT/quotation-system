<?php

namespace App\Application\Messaging;

use DateTimeImmutable;

/**
 * Represents a notification message intended to inform or alert users
 * about relevant events or actions within the application.
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
     * Creates a new NotificationMessage instance.
     *
     * @param string $text Main notification content.
     * @param array $context Optional additional contextual metadata.
     * @param string|null $code Optional categorization code.
     * @param DateTimeImmutable|null $timestamp Optional timestamp (defaults to now).
     * @return static
     */
    public static function create(string $text, array $context = [], ?string $code = null, ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, $code, $timestamp);
    }
}
