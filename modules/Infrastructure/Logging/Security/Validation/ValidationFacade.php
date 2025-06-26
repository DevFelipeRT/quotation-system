<?php

declare(strict_types=1);

namespace Logging\Security\Validation;

use DateTimeImmutable;
use Logging\Domain\Security\Contract\ValidatorInterface;
use Logging\Security\Validation\Services\ChannelValidator;
use Logging\Security\Validation\Services\ContextValidator;
use Logging\Security\Validation\Services\DirectoryValidator;
use Logging\Security\Validation\Services\LevelValidator;
use Logging\Security\Validation\Services\MessageValidator;
use Logging\Security\Validation\Services\TimestampValidator;

/**
 * ValidationFacade
 *
 * Provides a unified and decoupled interface for validating all log-related value objects.
 *
 * This facade aggregates a set of dedicated validator services, each responsible for enforcing
 * the integrity and normalization rules of a specific aspect of the logging domain
 * (channel, context, directory, level, message, timestamp). Each specialized validator is injected
 * via the constructor, promoting flexibility, testability, and strict separation of concerns.
 *
 * All validation logic required by domain objects is exposed through this class, which fully
 * implements the {@see ValidatorInterface} contract and delegates each validation operation to the
 * corresponding service.
 *
 * Architectural notes:
 * - Follows the Facade Pattern, abstracting the complexity of multiple validation routines.
 * - Ensures that all validation logic is centralized and consistently enforced throughout the infrastructure layer.
 * - Promotes compliance with SOLID principles, especially Single Responsibility and Dependency Inversion.
 *
 * @see ValidatorInterface
 */
final class ValidationFacade implements ValidatorInterface
{
    /**
     * @var ChannelValidator Responsible for validation of channel names.
     */
    private ChannelValidator $channelValidator;

    /**
     * @var ContextValidator Responsible for validation of context arrays.
     */
    private ContextValidator $contextValidator;

    /**
     * @var DirectoryValidator Responsible for validation of log directory paths.
     */
    private DirectoryValidator $directoryValidator;

    /**
     * @var LevelValidator Responsible for validation of log levels.
     */
    private LevelValidator $levelValidator;

    /**
     * @var MessageValidator Responsible for validation of log messages and generic strings.
     */
    private MessageValidator $messageValidator;

    /**
     * @var TimestampValidator Responsible for validation of timestamp values.
     */
    private TimestampValidator $timestampValidator;

    /**
     * Constructs the ValidationFacade, injecting all specialized validator dependencies.
     *
     * @param ChannelValidator   $channelValidator    Validator for channel names.
     * @param ContextValidator   $contextValidator    Validator for context arrays.
     * @param DirectoryValidator $directoryValidator  Validator for log directory paths.
     * @param LevelValidator     $levelValidator      Validator for log levels.
     * @param MessageValidator   $messageValidator    Validator for log messages and strings.
     * @param TimestampValidator $timestampValidator  Validator for timestamps.
     */
    public function __construct(
        ChannelValidator $channelValidator,
        ContextValidator $contextValidator,
        DirectoryValidator $directoryValidator,
        LevelValidator $levelValidator,
        MessageValidator $messageValidator,
        TimestampValidator $timestampValidator
    ) {
        $this->channelValidator   = $channelValidator;
        $this->contextValidator   = $contextValidator;
        $this->directoryValidator = $directoryValidator;
        $this->levelValidator     = $levelValidator;
        $this->messageValidator   = $messageValidator;
        $this->timestampValidator = $timestampValidator;
    }

    /**
     * Validates and normalizes a channel name.
     *
     * @param string $channel Channel name to validate.
     * @return string         Normalized channel name.
     *
     * @throws InvalidLogChannelException
     */
    public function validateChannel(string $channel): string
    {
        return $this->channelValidator->validate($channel);
    }

    /**
     * Validates and normalizes an associative context array.
     *
     * Ensures all keys and values conform to domain constraints for log context data.
     *
     * @param array $context           Associative array to validate.
     * @return array<string, string>   Normalized context array.
     *
     * @throws InvalidLogContextException
     */
    public function validateContext(array $context): array
    {
        return $this->contextValidator->validate($context);
    }

    /**
     * Validates and normalizes a directory path used for log storage.
     *
     * @param string $path Directory path to validate.
     * @return string      Normalized directory path.
     *
     * @throws InvalidLogDirectoryException
     */
    public function validateDirectory(string $path): string
    {
        return $this->directoryValidator->validate($path);
    }

    /**
     * Validates and normalizes a log level.
     *
     * Ensures the provided log level is one of the allowed domain values.
     *
     * @param string $level        Log level to validate.
     * @param string[] $allowedLevels List of allowed levels.
     * @return string              Normalized log level.
     *
     * @throws InvalidLogLevelException
     */
    public function validateLevel(string $level, array $allowedLevels): string
    {
        return $this->levelValidator->validate($level, $allowedLevels);
    }

    /**
     * Validates and normalizes a log message.
     *
     * @param string $message     Log message to validate.
     * @param int|null $maxLength Maximum allowed length, or null for default.
     * @return string             Normalized log message.
     *
     * @throws InvalidLogMessageException
     */
    public function validateMessage(string $message, ?int $maxLength = null): string
    {
        return $this->messageValidator->validate($message, $maxLength);
    }

    /**
     * Validates and normalizes a timestamp value.
     *
     * Ensures the provided value is a valid DateTimeImmutable for logging purposes.
     *
     * @param mixed $date               Value to validate as a timestamp.
     * @return DateTimeImmutable        Normalized DateTimeImmutable instance.
     *
     * @throws InvalidArgumentException
     */
    public function validateTimestamp(mixed $date): DateTimeImmutable
    {
        return $this->timestampValidator->validate($date);
    }
}
