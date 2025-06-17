<?php

declare(strict_types=1);

namespace Logging\Domain\Security\Contract;

/**
 * Facade for all domain security operations, centralizing validation and sanitization routines.
 *
 * All log-related Value Objects MUST use this interface to ensure safe and consistent handling of input data.
 */
interface LogSecurityInterface
{
    /**
     * Sanitizes sensitive keys and values from the provided input array,
     * returning a copy with all confidential data masked or removed.
     *
     * This method MUST be called by all log-related Value Objects prior to
     * storing, exposing, or transmitting data.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed> Sanitized array safe for logging or export.
     */
    public function sanitize(array $input): array;

    /**
     * Validates and normalizes a generic domain string.
     *
     * @param string    $value
     * @param bool      $allowEmpty
     * @param int|null  $maxLength
     * @param bool      $allowWhitespace
     * @return string
     */
    public function validateString(
        string $value,
        bool $allowEmpty = false,
        ?int $maxLength = null,
        bool $allowWhitespace = true
    ): string;

    /**
     * Validates and normalizes a channel name.
     *
     * @param string $channel
     * @return string
     */
    public function validateChannel(string $channel): string;

    /**
     * Validates an associative context array.
     *
     * Ensures all keys and values are non-empty strings and meet domain constraints.
     *
     * @param array $context
     * @return array<string, string>
     */
    public function validateContext(array $context): array;

    /**
     * Validates a directory path.
     *
     * Ensures the path is non-empty, does not contain illegal characters,
     * and is not a root or traversal path.
     *
     * @param string $path
     * @return string
     */
    public function validateDirectory(string $path): string;

    /**
     * Validates and normalizes a log level.
     *
     * Ensures the level is in the list of allowed levels after normalization.
     *
     * @param string   $level
     * @param string[] $allowedLevels
     * @return string
     */
    public function validateLevel(string $level, array $allowedLevels): string;

    /**
     * Validates and normalizes a log message.
     *
     * Ensures the message is non-empty, within the allowed length, and follows
     * formatting and normalization conventions defined by the domain.
     *
     * @param string $message
     * @param int    $maxLength
     * @return string
     */
    public function validateMessage(string $message, int $maxLength = 2000): string;

    /**
     * Validates and normalizes a DateTimeImmutable timestamp.
     *
     * Ensures the provided value is a valid DateTimeImmutable instance.
     *
     * @param mixed $date
     * @return \DateTimeImmutable
     */
    public function validateTimestamp($date): \DateTimeImmutable;
}
