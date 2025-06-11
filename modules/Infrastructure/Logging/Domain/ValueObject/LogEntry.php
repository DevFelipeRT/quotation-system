<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;
use Logging\Domain\Contract\LogEntryInterface;

/**
 * Represents a structured, immutable log entry.
 *
 * SECURITY STRATEGIES IMPLEMENTED:
 *  - Context sanitization (masks known sensitive keys)
 *  - Strict validation of message, channel, and context
 *  - Defensive handling against code injection via input validation
 *  - Immutability enforcement (no mutators)
 *  - Blocks PHP serialization for log objects
 *
 * Usage Note: Never log full request bodies, passwords, tokens or PII directly. 
 * The context array is always filtered for sensitive keys.
 */
final class LogEntry implements LogEntryInterface
{
    /** @var array<string> */
    private const SENSITIVE_KEYS = [
        'password', 'pwd', 'pass', 'secret', 'token', 'access_token', 'refresh_token', 'credit_card', 'ssn'
    ];
    private LogLevelEnum $level;
    private string $message;
    private array $context;
    private ?string $channel;
    private DateTimeImmutable $timestamp;

    /**
     * @param LogLevelEnum             $level     Log severity level.
     * @param string                   $message   Log message text.
     * @param array<string, mixed>     $context   Optional contextual information.
     * @param string|null              $channel   Optional channel categorization (e.g., 'auth', 'payment').
     * @param DateTimeImmutable|null   $timestamp Time of log creation (defaults to now).
     */
    public function __construct(
        LogLevelEnum $level,
        string $message,
        array $context = [],
        ?string $channel = null,
        ?DateTimeImmutable $timestamp = null
    ) {
        // Message must be non-empty, printable, and not suspiciously long
        if (trim($message) === '' || mb_strlen($message) > 2048) {
            throw new InvalidArgumentException('Log message cannot be empty or excessively long.');
        }
        // Prevent common code injection patterns in message
        if (preg_match('/<\?php|<script|eval\(|base64_decode|system\(|exec\(/i', $message)) {
            throw new InvalidArgumentException('Log message contains potentially dangerous patterns.');
        }
        // Channel: optional but if present must be a safe, short identifier
        if ($channel !== null && !preg_match('/^[a-zA-Z0-9_\-.]{1,64}$/', $channel)) {
            throw new InvalidArgumentException('Channel name contains invalid characters.');
        }
        // Sanitize context for known sensitive keys
        $this->context = self::sanitizeContext($context);
        $this->level     = $level;
        $this->message   = $message;
        $this->channel   = $channel;
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    public function getLevel(): LogLevelEnum
    {
        return $this->level;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns context with sensitive keys masked.
     * Use with care in display or output.
     */
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
     * Sanitize sensitive information in context.
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private static function sanitizeContext(array $context): array
    {
        $sanitized = [];
        foreach ($context as $key => $value) {
            if (in_array(mb_strtolower($key), self::SENSITIVE_KEYS, true)) {
                $sanitized[$key] = '[MASKED]';
            } else {
                $sanitized[$key] = is_scalar($value) ? $value : '[NON_SCALAR]';
            }
        }
        return $sanitized;
    }

    /**
     * Block PHP serialization/deserialization for extra safety.
     */
    public function __serialize(): array
    {
        throw new \LogicException('Serialization of LogEntry objects is not permitted for security reasons.');
    }

    public function __unserialize(array $data): void
    {
        throw new \LogicException('Unserialization of LogEntry objects is not permitted for security reasons.');
    }
}
