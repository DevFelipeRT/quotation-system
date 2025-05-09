<?php

declare(strict_types=1);

namespace App\Application\Messaging\Application;

use App\Application\Messaging\Domain\MessageInterface;
use DateTimeImmutable;

/**
 * Abstract base class for structured, immutable application messages.
 *
 * Implements the core behavior shared by all message types:
 * - Content, context, code and timestamp encapsulation
 * - Optional serialization and timestamp formatting helpers
 */
abstract class AbstractMessage implements MessageInterface
{
    private string $message;
    private array $context;
    private ?string $code;
    private DateTimeImmutable $timestamp;

    /**
     * @param string $message Human-readable message text.
     * @param array<string, mixed> $context Optional contextual data.
     * @param string|null $code Optional classification or severity code.
     * @param DateTimeImmutable|null $timestamp Defaults to current time if omitted.
     */
    public function __construct(
        string $message,
        array $context = [],
        ?string $code = null,
        ?DateTimeImmutable $timestamp = null
    ) {
        $this->message   = $message;
        $this->context   = $context;
        $this->code      = $code;
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Returns the timestamp in ISO 8601 format (ATOM).
     *
     * @return string
     */
    public function formattedTimestamp(): string
    {
        return $this->timestamp->format(DATE_ATOM);
    }

    /**
     * Converts this message to an associative array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type'      => $this->getType(),
            'message'   => $this->message,
            'context'   => $this->context,
            'code'      => $this->code,
            'timestamp' => $this->formattedTimestamp(),
        ];
    }

    /**
     * Must return a unique identifier for the message type.
     *
     * @return string
     */
    abstract public function getType(): string;
}
