<?php

declare(strict_types=1);

namespace Logging\Domain\Security;

use DateTimeImmutable;
use Logging\Domain\Security\Contract\LogSecurityInterface;
use Logging\Domain\Security\Contract\SanitizerInterface;
use Logging\Domain\Security\Contract\ValidatorInterface;

/**
 * Facade for all domain-level security operations within the logging subsystem.
 *
 * This class centralizes validation and sanitization routines, providing
 * a unified API for log-related Value Objects and services. It delegates
 * all logic to specialized services, ensuring consistency, security, and
 * clear separation of concerns.
 *
 * All validation methods are prefixed with "validate" to explicitly convey
 * their purpose and side effects. The sanitize method must be used to protect
 * sensitive or confidential information before data is persisted, transmitted,
 * or exposed externally.
 *
 * This class is designed for extensibility, testability, and strict compliance
 * with Clean Architecture and SOLID principles.
 */
final class LogSecurity implements LogSecurityInterface
{
    private ValidatorInterface $validator;
    private SanitizerInterface $sanitizer;

    /**
     * Constructs the LogSecurity facade, injecting the validator and sanitizer dependencies.
     *
     * @param ValidatorInterface $validator  Service responsible for all domain validation logic.
     * @param SanitizerInterface $sanitizer  Service responsible for sanitization of sensitive data.
     */
    public function __construct(
        ValidatorInterface $validator,
        SanitizerInterface $sanitizer
    ) {
        $this->validator = $validator;
        $this->sanitizer = $sanitizer;
    }

    /**
     * Sanitizes sensitive data from any input value.
     *
     * Returns a sanitized version of the input, masking or removing confidential data
     * according to the domain policy. Arrays and objects are sanitized recursively.
     * Strings são tratados individualmente; outros tipos escalares são retornados sem alteração.
     *
     * @param mixed $input
     * @param string|null $maskToken Optional custom mask token; if null, uses the default.
     * @return mixed Sanitized value, of the same type as input.
     */
    public function sanitize(mixed $input, ?string $maskToken = null): mixed
    {
        return $this->sanitizer->sanitize($input, $maskToken);
    }

    /**
     * Determines whether a given value contains sensitive information according to the sanitizer's patterns.
     *
     * This method does not perform any mutation or sanitization. It only evaluates the provided value.
     * Keys of arrays and objects are not considered—only values are evaluated.
     *
     * @param mixed $value Input value to be analyzed (string, array, object, or scalar).
     * @return bool True if the value or any of its nested elements is considered sensitive; otherwise, false.
     */
    public function isSensitive(mixed $value): bool
    {
        return $this->sanitizer->isSensitive($value);
    }

    /**
     * Validates and normalizes a channel name.
     *
     * Ensures the channel is non-empty and contains only valid characters.
     *
     * @param string $channel
     * @return string
     */
    public function validateChannel(string $channel): string
    {
        return $this->validator->validateChannel($channel);
    }

    /**
     * Validates an associative context array.
     *
     * Ensures all keys and values are non-empty strings and meet domain constraints.
     *
     * @param array $context
     * @return array<string, string>
     */
    public function validateContext(array $context): array
    {
        return $this->validator->validateContext($context);
    }

    /**
     * Validates a directory path.
     *
     * Ensures the path is non-empty, does not contain illegal characters,
     * and is not a root or traversal path.
     *
     * @param string $path
     * @return string
     */
    public function validateDirectory(string $path): string
    {
        return $this->validator->validateDirectory($path);
    }

    /**
     * Validates and normalizes a log level.
     *
     * Ensures the level is in the list of allowed levels after normalization.
     *
     * @param string   $level
     * @param string[] $allowedLevels
     * @return string
     */
    public function validateLevel(string $level, array $allowedLevels): string
    {
        return $this->validator->validateLevel($level, $allowedLevels);
    }

    /**
     * Validates and normalizes a log message.
     *
     * Ensures the message is non-empty, within the allowed length, and follows
     * formatting and normalization conventions defined by the domain.
     *
     * @param string   $message
     * @param int|null $maxLength
     * @return string
     */
    public function validateMessage(string $message, ?int $maxLength = null): string
    {
        return $this->validator->validateMessage($message, $maxLength);
    }

    /**
     * Validates and normalizes a DateTimeImmutable timestamp.
     *
     * Ensures the provided value is a valid DateTimeImmutable instance.
     *
     * @param mixed $date
     * @return DateTimeImmutable
     */
    public function validateTimestamp($date): DateTimeImmutable
    {
        return $this->validator->validateTimestamp($date);
    }
}
