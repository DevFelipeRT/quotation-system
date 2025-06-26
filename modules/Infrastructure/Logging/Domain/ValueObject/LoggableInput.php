<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use DateTimeImmutable;
use Logging\Domain\Exception\InvalidLoggableInputException;
use Logging\Domain\ValueObject\Contract\LoggableInputInterface;

/**
 * Represents an immutable value object for log entry input.
 *
 * This object encapsulates all log entry attributes, guaranteeing strict validation
 * and immutability. Once instantiated, the properties cannot be altered.
 *
 * Responsibilities:
 * - Encapsulates a loggable input with message, level, context, channel, and timestamp.
 * - Enforces strict validation for all properties.
 * - Provides a consistent interface for transferring log data within the system.
 *
 * Immutability:
 * All properties are declared as readonly and initialized exclusively via the constructor.
 *
 * Exceptions:
 * Throws InvalidLoggableInputException for any property that fails validation.
 *
 * @see LoggableInputInterface
 */
final class LoggableInput implements LoggableInputInterface
{
    /**
     * The log message. Must be a non-empty string.
     *
     * @var string
     */
    private readonly string $message;

    /**
     * The log level (e.g., 'info', 'warning', 'error'). May be null.
     *
     * @var string|null
     */
    private readonly ?string $level;

    /**
     * The associative context array for the log entry.
     *
     * @var array<string, string>
     */
    private readonly array $context;

    /**
     * The channel or category for the log entry. May be null.
     *
     * @var string|null
     */
    private readonly ?string $channel;

    /**
     * The timestamp for the log entry.
     *
     * @var DateTimeImmutable
     */
    private readonly DateTimeImmutable $timestamp;

    /**
     * Constructs a LoggableInput instance, enforcing strict validation.
     *
     * @param string                     $message   Log message (non-empty string).
     * @param string|null                $level     Log level (e.g., 'info', 'error'). May be null.
     * @param array<string, string>|null $context   Context array. If provided, keys and values must be non-empty strings.
     * @param string|null                $channel   Log channel/category. May be null.
     * @param DateTimeImmutable|null     $timestamp Log entry timestamp. If null, defaults to current time.
     *
     * @throws InvalidLoggableInputException If any argument fails validation.
     */
    public function __construct(
        string $message,
        ?string $level = null,
        ?array $context = null,
        ?string $channel = null,
        ?DateTimeImmutable $timestamp = null
    ) {
        $this->message = $this->validateMessage($message);
        $this->level = $this->validateLevel($level);
        $this->context = $this->validateContext($context ?? []);
        $this->channel = $this->validateChannel($channel);
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    /**
     * Returns the log message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the log level code.
     *
     * @return string|null
     */
    public function getLevel(): ?string
    {
        return $this->level;
    }

    /**
     * Returns the context array for the log entry.
     *
     * @return array<string, string>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Returns the channel or category for the log entry.
     *
     * @return string|null
     */
    public function getChannel(): ?string
    {
        return $this->channel;
    }

    /**
     * Returns the timestamp for the log entry.
     *
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Validates the log message (non-empty string).
     *
     * @param string $message
     * @return string
     * @throws InvalidLoggableInputException If the message is empty.
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
     * Validates the log level (non-empty string or null).
     *
     * @param string|null $level
     * @return string|null
     * @throws InvalidLoggableInputException If the level is an empty string.
     */
    private function validateLevel(?string $level): ?string
    {
        if ($level === null) {
            return null;
        }
        $trimmed = trim($level);
        if ($trimmed === '') {
            throw InvalidLoggableInputException::emptyLevel();
        }
        return $trimmed;
    }

    /**
     * Validates the log context array.
     * Keys and values must be non-empty strings.
     *
     * @param array $context
     * @return array<string, string>
     * @throws InvalidLoggableInputException If any key or value is invalid.
     */
    private function validateContext(array $context): array
    {
        $context = $this->serializeContextValues($context);
        foreach ($context as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                throw InvalidLoggableInputException::invalidContextKey($key);
            }
            if (!is_string($value) || trim($value) === '') {
                throw InvalidLoggableInputException::invalidContextValue($key);
            }
        }
        return $context;
    }

    /**
     * Validates the log channel (non-empty string or null).
     *
     * @param string|null $channel
     * @return string|null
     * @throws InvalidLoggableInputException If the channel is an empty string.
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

    /**
     * Serializes any non-scalar value in the context array to string.
     *
     * Arrays and objects are converted to JSON; other types to string.
     *
     * @param array<string, mixed> $context
     * @return array<string, string|int|float|bool|null>
     */
    private function serializeContextValues(array $context): array
    {
        foreach ($context as $key => $value) {
            if (
                is_string($value) ||
                is_int($value) ||
                is_float($value) ||
                is_bool($value) ||
                is_null($value)
            ) {
                continue;
            }
            if (is_array($value) || is_object($value)) {
                $context[$key] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
            } else {
                // Fallback for resources, etc.
                $context[$key] = (string)$value;
            }
        }
        return $context;
    }
}
