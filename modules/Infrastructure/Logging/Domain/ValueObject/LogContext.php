<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use Logging\Domain\Security\Contract\LogSanitizerInterface;
use Logging\Domain\Exception\InvalidLogContextException;

/**
 * Value Object representing a log context.
 * Enforces safe, validated, immutable associative array, always sanitized via LogSanitizer.
 */
final class LogContext
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * @param array<mixed> $context
     * @param LogSanitizerInterface $sanitizer
     * @throws InvalidLogContextException
     */
    public function __construct(array $context, LogSanitizerInterface $sanitizer)
    {
        $sanitized = $sanitizer->sanitize($context);
        $this->context = $this->validateContext($sanitized);
    }

    /**
     * Returns the associative context array (always string keys).
     *
     * @return array<string, mixed>
     */
    public function value(): array
    {
        return $this->context;
    }

    /**
     * Returns value for a given key or null.
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
     * Validates the context array after sanitization.
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     * @throws InvalidLogContextException
     */
    private function validateContext(array $context): array
    {
        $result = [];
        foreach ($context as $key => $value) {
            $this->assertKeyIsString($key);
            $trimmedKey = $this->assertKeyContent($key);
            $this->assertKeyNotDuplicate($trimmedKey, $result);
            $this->assertValidValue($trimmedKey, $value);
            $result[$trimmedKey] = $value;
        }
        return $result;
    }

    /**
     * @param mixed $key
     * @throws InvalidLogContextException
     */
    private function assertKeyIsString($key): void
    {
        if (!is_string($key)) {
            throw InvalidLogContextException::invalidKeyType($key);
        }
    }

    /**
     * @param string $key
     * @return string
     * @throws InvalidLogContextException
     */
    private function assertKeyContent(string $key): string
    {
        $trimmedKey = trim($key);
        if ($trimmedKey === '' || preg_match('/[\x00-\x1F\x7F]/', $trimmedKey)) {
            throw InvalidLogContextException::invalidKeyContent($key);
        }
        return $trimmedKey;
    }

    /**
     * @param string $trimmedKey
     * @param array<string, mixed> $result
     * @throws InvalidLogContextException
     */
    private function assertKeyNotDuplicate(string $trimmedKey, array $result): void
    {
        if (array_key_exists($trimmedKey, $result)) {
            throw InvalidLogContextException::duplicateKey($trimmedKey);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws InvalidLogContextException
     */
    private function assertValidValue(string $key, $value): void
    {
        if (
            !is_scalar($value)
            && $value !== null
            && !is_array($value)
        ) {
            throw InvalidLogContextException::invalidValueType($key, $value);
        }
    }
}
