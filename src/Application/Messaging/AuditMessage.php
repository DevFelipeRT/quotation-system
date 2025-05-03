<?php

namespace App\Application\Messaging;

use DateTimeImmutable;

/**
 * Represents a semantically structured audit message generated within the application layer.
 *
 * Used to record significant actions for compliance, tracing, or security auditing purposes.
 */
final class AuditMessage extends AbstractMessage
{
    /**
     * Returns the message type identifier.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'audit';
    }

    /**
     * Creates a new AuditMessage instance.
     *
     * @param string $text Main audit message text.
     * @param array $context Additional contextual metadata.
     * @param string|null $code Optional categorization code.
     * @param DateTimeImmutable|null $timestamp Optional timestamp (defaults to now).
     * @return static
     */
    public static function create(string $text, array $context = [], ?string $code = null, ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, $code, $timestamp);
    }
}
