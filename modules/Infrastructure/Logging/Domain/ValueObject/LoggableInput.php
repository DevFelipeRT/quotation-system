<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use DateTimeImmutable;
use Logging\Domain\Exception\InvalidLoggableInputException;
use PublicContracts\Logging\LoggableInputInterface;

/**
 * Value Object representing a loggable input for assembling a LogEntry.
 * All input data is validated and the object is strictly immutable.
 */
final class LoggableInput implements LoggableInputInterface
{
    private readonly ?string $code;
    private readonly string $message;
    private readonly array $context;
    private readonly ?string $channel;
    private readonly DateTimeImmutable $timestamp;

    /**
     * @param string|null $code Log level code (e.g., 'info', 'error')
     * @param string $message The log message (must be non-empty)
     * @param array<string, string> $context Associative context (string keys and string values only)
     * @param string|null $channel Optional log channel/category
     * @param DateTimeImmutable|null $timestamp Log timestamp, defaults to now
     *
     * @throws InvalidLoggableInputException If any value is invalid.
     */
    public function __construct(
        ?string $code,
        string $message,
        array $context = [],
        ?string $channel = null,
        ?DateTimeImmutable $timestamp = null
    ) {
        $this->code = $this->validateCode($code);
        $this->message = $this->validateMessage($message);
        $this->context = $this->validateContext($context);
        $this->channel = $this->validateChannel($channel);
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Validates the log level code.
     */
    private function validateCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }
        $trimmed = trim($code);
        if ($trimmed === '') {
            throw InvalidLoggableInputException::emptyCode();
        }
        return $trimmed;
    }

    /**
     * Validates the log message.
     */
    private function validateMessage(string $message): string
    {
        $trimmed = trim($message);
        if ($trimmed === '') {
            throw InvalidLoggableInputException::emptyMessage();
        }
        return $trimmed;
    }

    /**
     * Validates the log context: keys and values must be strings, keys non-empty.
     */
    private function validateContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                throw InvalidLoggableInputException::invalidContextKey($key);
            }
            if (!is_string($value)) {
                throw InvalidLoggableInputException::invalidContextValue($key);
            }
        }
        return $context;
    }

    /**
     * Validates the log channel.
     */
    private function validateChannel(?string $channel): ?string
    {
        if ($channel === null) {
            return null;
        }
        $trimmed = trim($channel);
        if ($trimmed === '') {
            throw InvalidLoggableInputException::emptyChannel();
        }
        return $trimmed;
    }
}
