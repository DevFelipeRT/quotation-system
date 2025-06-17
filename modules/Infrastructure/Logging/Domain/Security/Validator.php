<?php

declare(strict_types=1);

namespace Logging\Domain\Security;

use DateTimeImmutable;
use Logging\Domain\Exception\InvalidLoggableInputException;
use Logging\Domain\Exception\InvalidLogLevelException;
use Logging\Domain\Exception\InvalidLogMessageException;
use Logging\Domain\Exception\InvalidLogChannelException;
use Logging\Domain\Exception\InvalidLogContextException;
use Logging\Domain\Exception\InvalidLogDirectoryException;
use Logging\Domain\Security\Contract\ValidatorInterface;
use PublicContracts\Logging\ValidationConfigInterface;

/**
 * Validates all value objects within the logging domain.
 *
 * All validation rules and constraints are provided via a
 * ValidationConfigInterface. Each method throws a domain‐specific
 * exception if validation fails.
 */
final class Validator implements ValidatorInterface
{
    private readonly ValidationConfigInterface $config;

    /**
     * @param ValidationConfigInterface $config Configuration of all validation parameters.
     */
    public function __construct(ValidationConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Validates a generic domain string.
     *
     * @param string     $value
     * @param bool       $allowEmpty
     * @param int|null   $maxLength
     * @param bool       $allowWhitespace
     * @return string
     *
     * @throws InvalidLoggableInputException If the string is empty (when not allowed),
     *                                       contains forbidden characters, or exceeds length.
     */
    public function validateString(
        string $value,
        bool $allowEmpty = false,
        ?int $maxLength = null,
        bool $allowWhitespace = true
    ): string {
        $max   = $maxLength ?? $this->config->defaultStringMaxLength();
        $clean = $allowWhitespace
            ? trim($value)
            : preg_replace('/\s+/', '', $value);

        if (!$allowEmpty && $clean === '') {
            throw new InvalidLoggableInputException('Value must not be empty.');
        }
        if (preg_match($this->config->stringForbiddenCharsRegex(), $clean)) {
            throw new InvalidLoggableInputException('Value contains forbidden characters.');
        }
        if ($max !== null && mb_strlen($clean) > $max) {
            throw new InvalidLoggableInputException(
                sprintf('Value exceeds maximum length of %d characters.', $max)
            );
        }

        return $clean;
    }

    /**
     * Validates a log channel name.
     *
     * @param string $channel
     * @return string
     *
     * @throws InvalidLogChannelException If the channel is empty or contains invalid characters.
     */
    public function validateChannel(string $channel): string
    {
        $clean = trim($channel);

        if ($clean === '') {
            throw new InvalidLogChannelException('Channel cannot be empty.');
        }
        if (preg_match($this->config->stringForbiddenCharsRegex(), $clean)) {
            throw new InvalidLogChannelException('Channel contains invalid characters.');
        }

        return mb_strtolower($clean);
    }

    /**
     * Validates an associative context array.
     *
     * @param array $context
     * @return array<string, string>
     *
     * @throws InvalidLogContextException If any key or value is invalid.
     */
    public function validateContext(array $context): array
    {
        $maxKey   = $this->config->contextKeyMaxLength();
        $maxValue = $this->config->contextValueMaxLength();
        $forbid   = $this->config->stringForbiddenCharsRegex();
        $result   = [];

        foreach ($context as $key => $value) {
            if (!is_string($key)
                || trim($key) === ''
                || mb_strlen($key) > $maxKey
                || preg_match($forbid, $key)
            ) {
                throw new InvalidLogContextException(
                    sprintf('Invalid context key "%s".', $key)
                );
            }

            if (!is_scalar($value) && $value !== null) {
                throw new InvalidLogContextException(
                    sprintf('Context value for key "%s" must be scalar or null.', $key)
                );
            }

            $strVal = (string)$value;
            if (($strVal === '' && $value !== 0 && $value !== false)
                || mb_strlen($strVal) > $maxValue
                || preg_match($forbid, $strVal)
            ) {
                throw new InvalidLogContextException(
                    sprintf('Invalid context value for key "%s".', $key)
                );
            }

            $result[$key] = $strVal;
        }

        return $result;
    }

    /**
     * Validates a directory path.
     *
     * @param string $path
     * @return string
     *
     * @throws InvalidLogDirectoryException If the path is empty, root, contains traversal, or invalid chars.
     */
    public function validateDirectory(string $path): string
    {
        $clean = rtrim(str_replace("\0", '', trim($path)), "/\\");

        if ($clean === '' || $clean === $this->config->directoryRootString()) {
            throw new InvalidLogDirectoryException('Directory path cannot be empty or root.');
        }
        if (strpos($clean, $this->config->directoryTraversalString()) !== false) {
            throw new InvalidLogDirectoryException('Directory path contains parent traversal.');
        }
        if (preg_match($this->config->stringForbiddenCharsRegex(), $clean)) {
            throw new InvalidLogDirectoryException('Directory path contains invalid characters.');
        }

        return $clean;
    }

    /**
     * Validates a log level.
     *
     * @param string   $level
     * @param string[] $allowedLevels
     * @return string
     *
     * @throws InvalidLogLevelException If the level is empty or not in the allowed set.
     */
    public function validateLevel(string $level, array $allowedLevels): string
    {
        $norm = mb_strtolower(trim($level));

        if ($norm === '') {
            throw new InvalidLogLevelException('Log level cannot be empty.');
        }
        if (!in_array($norm, $allowedLevels, true)) {
            throw new InvalidLogLevelException(
                sprintf('Invalid log level "%s".', $norm)
            );
        }

        return $norm;
    }

    /**
     * Validates a log message.
     *
     * @param string   $message
     * @param int|null $maxLength
     * @return string
     *
     * @throws InvalidLogMessageException If the message is invalid, too long, or violates formatting rules.
     */
    public function validateMessage(string $message, int $maxLength = null): string
    {
        $max = $maxLength ?? $this->config->logMessageMaxLength();
        $msg = trim($message);

        if ($msg === '') {
            throw new InvalidLogMessageException('Message cannot be empty.');
        }
        if (preg_match($this->config->stringForbiddenCharsRegex(), $msg)) {
            throw new InvalidLogMessageException('Message contains invalid characters.');
        }
        if (mb_strlen($msg) > $max) {
            throw new InvalidLogMessageException(
                sprintf('Message exceeds maximum length of %d.', $max)
            );
        }

        // Capitalize first letter
        $msg = mb_strtoupper(mb_substr($msg, 0, 1)) . mb_substr($msg, 1);

        // Append terminal punctuation if missing
        if (!preg_match($this->config->logMessageTerminalPunctuationRegex(), $msg)) {
            $msg .= '.';
        }

        return $msg;
    }

    /**
     * Validates a DateTimeImmutable timestamp.
     *
     * @param mixed $date
     * @return DateTimeImmutable
     *
     * @throws InvalidLogMessageException If the timestamp is invalid.
     */
    public function validateTimestamp($date): DateTimeImmutable
    {
        if (!($date instanceof DateTimeImmutable)) {
            throw new InvalidLogMessageException('Invalid timestamp object.');
        }

        return $date;
    }
}
