<?php

declare(strict_types=1);

namespace Logging\Domain\Security;

use Logging\Domain\Exception\InvalidLogChannelException;
use Logging\Domain\Exception\InvalidLogContextException;
use Logging\Domain\Exception\InvalidLogDirectoryException;
use Logging\Domain\Exception\InvalidLogLevelException;
use Logging\Domain\Exception\InvalidLogMessageException;

/**
 * Centralized validation utility for domain Value Objects.
 * All methods throw domain-specific exceptions on failure.
 */
final class Validator
{
    /**
     * Validates a generic string for the domain.
     *
     * @param string      $value
     * @param bool        $allowEmpty
     * @param int|null    $maxLength
     * @param bool        $allowWhitespace
     * @return string
     */
    public static function string(
        string $value,
        bool $allowEmpty = false,
        ?int $maxLength = null,
        bool $allowWhitespace = true
    ): string {
        $val = $allowWhitespace ? trim($value) : preg_replace('/\s+/', '', $value);

        if (!$allowEmpty && $val === '') {
            throw InvalidLogMessageException::empty();
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $val)) {
            throw InvalidLogMessageException::invalidCharacters();
        }
        if ($maxLength !== null && mb_strlen($val) > $maxLength) {
            throw InvalidLogMessageException::tooLong();
        }
        return $val;
    }

    /**
     * Validates a channel name: non-empty, no invalid chars, normalized to lowercase.
     *
     * @param string $channel
     * @return string
     */
    public static function channel(string $channel): string
    {
        $value = trim($channel);
        if ($value === '') {
            throw InvalidLogChannelException::empty();
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $value)) {
            throw InvalidLogChannelException::invalidCharacters();
        }
        if (mb_strlen($value) > 128) {
            throw InvalidLogChannelException::tooLong();
        }
        return mb_strtolower($value);
    }

    /**
     * Validates and normalizes a log context associative array.
     *
     * All keys must be non-empty strings without control characters.
     * All values must be scalar or null; after conversion to string, values must not be empty 
     * (unless the original value is 0 or false) and must not contain control characters.
     *
     * @param array $context Associative array of context data.
     * @return array<string, string> Normalized context (string keys and string values).
     *
     * @throws InvalidLogContextException If any key or value is invalid.
     */
    public static function context(array $context): array
    {
        $result = [];
        $seenKeys = [];
        foreach ($context as $key => $value) {
            // Key validation
            if (!is_string($key)) {
                throw InvalidLogContextException::invalidKeyType($key);
            }
            if (trim($key) === '' || preg_match('/[\x00-\x1F\x7F]/', $key)) {
                throw InvalidLogContextException::invalidKeyContent($key);
            }
            if (in_array($key, $seenKeys, true)) {
                throw InvalidLogContextException::duplicateKey($key);
            }
            $seenKeys[] = $key;

            // Value validation
            if (!is_scalar($value) && $value !== null) {
                throw InvalidLogContextException::invalidValueType($key, $value);
            }
            $strVal = (string) $value;
            if ($strVal === '' && $value !== 0 && $value !== false) {
                throw InvalidLogContextException::invalidValueContent($key);
            }
            if (preg_match('/[\x00-\x1F\x7F]/', $strVal)) {
                throw InvalidLogContextException::invalidValueContent($key);
            }
            $result[$key] = $strVal;
        }
        return $result;
    }

    /**
     * Validates a directory path: non-empty, no null byte, no traversal, not root.
     *
     * @param string $path
     * @return string
     */
    public static function directory(string $path): string
    {
        $clean = rtrim(str_replace("\0", '', trim($path)), "/\\");
        if ($clean === '' || $clean === '/') {
            throw InvalidLogDirectoryException::empty();
        }
        if (strpos($clean, '..') !== false) {
            throw InvalidLogDirectoryException::unsafe('parent directory traversal not allowed');
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $clean)) {
            throw InvalidLogDirectoryException::unsafe('contains invalid characters');
        }
        return $clean;
    }

    /**
     * Validates a log level: non-empty, lowercased, and in allowed set.
     *
     * @param string   $level
     * @param string[] $allowedLevels
     * @return string
     */
    public static function level(string $level, array $allowedLevels): string
    {
        $norm = mb_strtolower(trim($level));
        if ($norm === '') {
            throw InvalidLogLevelException::forLevel($level);
        }
        if (!in_array($norm, $allowedLevels, true)) {
            throw InvalidLogLevelException::forLevel($norm);
        }
        return $norm;
    }

    /**
     * Validates a log message: non-empty, limited, normalized (capitalizes first, ends with period).
     *
     * @param string $message
     * @param int    $maxLength
     * @return string
     */
    public static function message(string $message, int $maxLength = 2000): string
    {
        $msg = trim($message);
        if ($msg === '') {
            throw InvalidLogMessageException::empty();
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $msg)) {
            throw InvalidLogMessageException::invalidCharacters();
        }
        if (mb_strlen($msg) > $maxLength) {
            throw InvalidLogMessageException::tooLong();
        }
        // Capitalize first letter
        $msg = mb_strtoupper(mb_substr($msg, 0, 1)) . mb_substr($msg, 1);
        // Add period if needed
        if (!preg_match('/[.!?]$/u', $msg)) {
            $msg .= '.';
        }
        return $msg;
    }

    /**
     * Validates an instance of DateTimeImmutable.
     *
     * @param mixed $date
     * @return \DateTimeImmutable
     */
    public static function timestamp($date): \DateTimeImmutable
    {
        if ($date instanceof \DateTimeImmutable) {
            return $date;
        }
        // Pode ser criado uma exceção específica se desejar
        throw new \InvalidArgumentException('Invalid timestamp object.');
    }
}
