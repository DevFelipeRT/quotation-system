<?php

declare(strict_types=1);

namespace Logging\Domain\Security\Contract;

use DateTimeImmutable;
use Logging\Domain\Exception\InvalidLogMessageException;
use Logging\Domain\Exception\InvalidLogLevelException;
use Logging\Domain\Exception\InvalidLogContextException;
use Logging\Domain\Exception\InvalidLogChannelException;
use Logging\Domain\Exception\InvalidLogDirectoryException;
use Logging\Domain\Exception\InvalidLoggableInputException;

/**
 * Domain contract for validation of log-related input data.
 *
 * Implementations are responsible for enforcing all invariants and business rules required
 * by Value Objects within the logging domain, ensuring that only valid and normalized data
 * enters the system.
 *
 * Every validation method must throw a domain-specific exception if validation fails.
 */
interface ValidatorInterface
{
    /**
     * Validates and normalizes a channel name.
     *
     * @param string $channel Channel name to validate.
     * @return string Normalized channel name.
     *
     * @throws InvalidLogChannelException If validation fails.
     */
    public function validateChannel(string $channel): string;

    /**
     * Validates and normalizes a context associative array.
     *
     * All keys and values must conform to domain constraints and be non-empty strings.
     *
     * @param array $context Associative array context to validate.
     * @return array<string, string> Normalized context array.
     *
     * @throws InvalidLogContextException If validation fails.
     */
    public function validateContext(array $context): array;

    /**
     * Validates and normalizes a directory path intended for log storage.
     *
     * Ensures the path is non-empty, does not contain forbidden characters, and is not a traversal/root path.
     *
     * @param string $path Directory path to validate.
     * @return string Normalized directory path.
     *
     * @throws InvalidLogDirectoryException If validation fails.
     */
    public function validateDirectory(string $path): string;

    /**
     * Validates and normalizes a log level string.
     *
     * Ensures the log level is one of the allowed values after normalization.
     *
     * @param string $level Log level to validate.
     * @param string[] $allowedLevels List of allowed log levels.
     * @return string Normalized log level.
     *
     * @throws InvalidLogLevelException If validation fails.
     */
    public function validateLevel(string $level, array $allowedLevels): string;

    /**
     * Validates and normalizes a log message.
     *
     * Ensures the message is non-empty, within the allowed length, and normalized according to domain policy.
     *
     * @param string $message Log message to validate.
     * @param int|null $maxLength Optional maximum allowed message length.
     * @return string Normalized log message.
     *
     * @throws InvalidLogMessageException If validation fails.
     */
    public function validateMessage(string $message, ?int $maxLength = null): string;

    /**
     * Validates and normalizes a DateTimeImmutable timestamp.
     *
     * Ensures the value is a valid, immutable timestamp suitable for log events.
     *
     * @param mixed $date Value to validate as a timestamp.
     * @return DateTimeImmutable Normalized timestamp.
     *
     * @throws InvalidArgumentException If validation fails.
     */
    public function validateTimestamp(mixed $date): DateTimeImmutable;
}
