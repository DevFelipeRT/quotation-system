<?php

namespace App\Application\Messaging;

use DateTimeImmutable;

/**
 * Represents a structured error message generated within the application layer.
 *
 * Typically used to encapsulate error details for logging, auditing, or user feedback.
 */
final class ErrorMessage extends AbstractMessage
{
    /**
     * Returns the message type identifier.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'error';
    }

    /**
     * Creates a new ErrorMessage instance.
     *
     * @param string $text Main error description.
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
