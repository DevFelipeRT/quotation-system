<?php

declare(strict_types=1);

namespace Logging\Domain\Security\Contract;

use DateTimeImmutable;

/**
 * Contract for validation routines within the logging domain.
 *
 * Implementations MUST enforce all invariants and constraints required by
 * Value Objects, guaranteeing the integrity of all data entering the domain.
 *
 * All validation methods MUST throw \InvalidArgumentException in case of failure.
 */
interface ValidatorInterface
{
    /**
     * Validates and normalizes a generic domain string.
     *
     * @param string    $value
     * @param bool      $allowEmpty
     * @param int|null  $maxLength
     * @param bool      $allowWhitespace
     * @return string
     * @throws \InvalidArgumentException If validation fails.
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
     * @throws \InvalidArgumentException If validation fails.
     */
    public function validateChannel(string $channel): string;

    /**
     * Validates an associative context array.
     *
     * Ensures all keys and values are non-empty strings and meet domain constraints.
     *
     * @param array $context
     * @return array<string, string>
     * @throws \InvalidArgumentException If validation fails.
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
     * @throws \InvalidArgumentException If validation fails.
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
     * @throws \InvalidArgumentException If validation fails.
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
     * @throws \InvalidArgumentException If validation fails.
     */
    public function validateMessage(string $message, int $maxLength = 2000): string;

    /**
     * Validates and normalizes a DateTimeImmutable timestamp.
     *
     * Ensures the provided value is a valid DateTimeImmutable instance.
     *
     * @param mixed $date
     * @return DateTimeImmutable
     * @throws \InvalidArgumentException If validation fails.
     */
    public function validateTimestamp($date): DateTimeImmutable;
}
