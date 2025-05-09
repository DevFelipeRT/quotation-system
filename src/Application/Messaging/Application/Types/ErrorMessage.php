<?php

namespace App\Application\Messaging\Application\Types;

use App\Application\Messaging\Application\AbstractMessage;
use DateTimeImmutable;

/**
 * Represents a structured error message within the application.
 *
 * Encapsulates errors in a form suitable for logging, reporting,
 * user communication or auditing — with optional code and context.
 */
final class ErrorMessage extends AbstractMessage
{
    /**
     * Identifies this message as an error type.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'error';
    }

    /**
     * Creates a new error message instance.
     *
     * @param string $text Description of the error.
     * @param array<string, mixed> $context Optional additional metadata.
     * @param string|null $code Optional error code (e.g. 'E_DB_CONN').
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
