<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use Logging\Domain\Exception\InvalidLogLevelException;
use Logging\Domain\Security\Contract\LogSanitizerInterface;

/**
 * Value Object representing a log level.
 * All external input is validated and sanitized by the domain sanitizer.
 */
final class LogLevel
{
    private const DEFAULT_LEVELS = [
        'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'
    ];

    private string $level;

    /**
     * @var string[]
     */
    private array $validLevels;

    /**
     * @param string $level
     * @param LogSanitizerInterface $sanitizer
     * @param string[]|null $customLevels
     * @throws InvalidLogLevelException
     */
    public function __construct(
        string $level,
        LogSanitizerInterface $sanitizer,
        ?array $customLevels = null
    ) {
        $this->validLevels = $this->sanitizeAllLevels(
            array_merge(self::DEFAULT_LEVELS, $customLevels ?? []),
            $sanitizer
        );
        $this->level = $this->sanitizeLevel($level, $sanitizer);

        if (!in_array($this->level, $this->validLevels, true)) {
            throw InvalidLogLevelException::forLevel($level);
        }
    }

    /**
     * Returns the normalized string value of the log level.
     */
    public function value(): string
    {
        return $this->level;
    }

    /**
     * Returns all valid log levels for this instance.
     * @return string[]
     */
    public function validLevels(): array
    {
        return $this->validLevels;
    }

    /**
     * Compares two LogLevel instances for equality.
     */
    public function equals(LogLevel $other): bool
    {
        return $this->level === $other->level;
    }

    public function __toString(): string
    {
        return $this->level;
    }

    /**
     * Sanitizes and normalizes all provided log levels.
     *
     * @param string[] $levels
     * @param LogSanitizerInterface $sanitizer
     * @return string[]
     * @throws InvalidLogLevelException If no valid levels remain after sanitization.
     */
    private function sanitizeAllLevels(array $levels, LogSanitizerInterface $sanitizer): array
    {
        $sanitized = [];
        foreach ($levels as $level) {
            $norm = $this->sanitizeLevel($level, $sanitizer);
            if ($norm !== '' && !in_array($norm, $sanitized, true)) {
                $sanitized[] = $norm;
            }
        }
        if (empty($sanitized)) {
            throw InvalidLogLevelException::forLevel('[none]');
        }
        return $sanitized;
    }

    /**
     * Sanitizes and normalizes a single log level value.
     *
     * @param string $level
     * @param LogSanitizerInterface $sanitizer
     * @return string
     */
    private function sanitizeLevel(string $level, LogSanitizerInterface $sanitizer): string
    {
        $result = $sanitizer->sanitize(['level' => $level])['level'] ?? '';
        $result = mb_strtolower(trim((string)$result));
        return ($result === '' || preg_match('/[\x00-\x1F\x7F]/', $result)) ? '' : $result;
    }
}
