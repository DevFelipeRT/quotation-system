<?php

namespace App\Messaging\Application\Types;

use App\Messaging\Application\AbstractMessage;
use DateTimeImmutable;

/**
 * Represents an immutable audit message used for internal traceability.
 *
 * Typically used to log user actions, permission changes, access attempts,
 * or any domain-relevant operation that must be tracked for security,
 * compliance or analytics.
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
     * Creates a new audit message instance.
     *
     * @param string $text Describes the audited action.
     * @param array<string, mixed> $context Additional metadata (user, scope, entity, etc.).
     * @param string|null $code Optional classification code (e.g., 'PERMISSION_CHANGE').
     * @param DateTimeImmutable|null $timestamp Optional occurrence time (default: now).
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
