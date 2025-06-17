<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use Logging\Domain\Security\Contract\LogSecurityInterface;
use Logging\Domain\Exception\InvalidLogContextException;

/**
 * Immutable Value Object representing a log context.
 *
 * Encapsulates a sanitized and validated associative array,
 * leveraging domain-specific security and validation logic.
 * 
 * @immutable
 */
final class LogContext
{
    /**
     * @var array<string, mixed> The validated and sanitized context data.
     */
    private array $context;

    /**
     * Constructs a LogContext instance using security facade for validation and sanitization.
     *
     * @param array                 $context  The raw context data.
     * @param LogSecurityInterface  $security Domain security facade.
     *
     * @throws InvalidLogContextException If context validation fails.
     */
    public function __construct(array $context, LogSecurityInterface $security)
    {
        $this->context = $this->sanitizeAndValidate($context, $security);
    }

    /**
     * Returns the context array.
     *
     * @return array<string, mixed>
     */
    public function value(): array
    {
        return $this->context;
    }

    /**
     * Retrieves the value associated with the specified key, or null if not present.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return $this->context[$key] ?? null;
    }

    /**
     * Returns all context keys.
     *
     * @return string[]
     */
    public function keys(): array
    {
        return array_keys($this->context);
    }

    /**
     * Sanitizes and validates the context array.
     *
     * @param array                 $context
     * @param LogSecurityInterface  $security
     *
     * @return array<string, mixed>
     *
     * @throws InvalidLogContextException If validation rules are violated.
     */
    private function sanitizeAndValidate(array $context, LogSecurityInterface $security): array
    {
        $sanitized = $security->sanitize($context);

        return $security->validateContext($sanitized);
    }
}
