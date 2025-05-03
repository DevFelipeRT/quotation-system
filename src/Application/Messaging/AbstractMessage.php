<?php

namespace App\Application\Messaging;

use DateTimeImmutable;

/**
 * Abstract base class for immutable, structured system messages.
 */
abstract class AbstractMessage implements MessageInterface
{
    private string $message;
    private array $context;
    private ?string $code;
    private DateTimeImmutable $timestamp;

    /**
     * Constructs a new message instance.
     *
     * @param string $message Main message text.
     * @param array $context Additional context data.
     * @param string|null $code Optional message code.
     * @param DateTimeImmutable|null $timestamp Optional timestamp (defaults to now).
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

    /**
     * {@inheritdoc}
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function formattedTimestamp(): string
    {
        return $this->timestamp->format('Y-m-d H:i:s');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'type'      => $this->getType(),
            'message'   => $this->getMessage(),
            'context'   => $this->getContext(),
            'code'      => $this->getCode(),
            'timestamp' => $this->formattedTimestamp(),
        ];
    }

    /**
     * Returns the specific type identifier for the message.
     *
     * Each concrete subclass must implement this method.
     */
    abstract public function getType(): string;
}
