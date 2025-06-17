<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use Logging\Domain\Security\Contract\LogSecurityInterface;
use Logging\Domain\Exception\InvalidLogLevelException;

/**
 * Immutable Value Object representing a log level.
 *
 * Ensures validation and sanitization using centralized domain-specific security logic.
 */
final class LogLevel
{
    /**
     * Default standard PSR-3 log levels.
     *
     * @var string[]
     */
    private const DEFAULT_LEVELS = [
        'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'
    ];

    private string $level;

    /**
     * @var string[]
     */
    private array $validLevels;

    /**
     * Constructs a LogLevel instance validated against allowed levels.
     *
     * @param string                $level        Log level to validate.
     * @param LogSecurityInterface  $security     Security facade for sanitization and validation.
     * @param string[]|null         $customLevels Optional custom allowed levels.
     *
     * @throws InvalidLogLevelException If validation fails.
     */
    public function __construct(
        string $level,
        LogSecurityInterface $security,
        ?array $customLevels = null
    ) {
        $this->validLevels = $this->buildValidLevels($security, $customLevels);

        $sanitizedLevel = $security->sanitize(['level' => $level])['level'] ?? '';
        $this->level = $security->validateLevel($sanitizedLevel, $this->validLevels);
    }

    /**
     * Retrieves the validated log level.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->level;
    }

    /**
     * Checks equality with another LogLevel.
     *
     * @param LogLevel $other
     * @return bool
     */
    public function equals(LogLevel $other): bool
    {
        return $this->level === $other->level;
    }

    /**
     * Returns the list of valid log levels.
     *
     * @return string[]
     */
    public function validLevels(): array
    {
        return $this->validLevels;
    }

    /**
     * String representation of the log level.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->level;
    }

    /**
     * Builds and validates the complete list of valid log levels.
     *
     * @param LogSecurityInterface $security
     * @param string[]|null $customLevels
     *
     * @return string[]
     *
     * @throws InvalidLogLevelException If any custom level is invalid.
     */
    private function buildValidLevels(LogSecurityInterface $security, ?array $customLevels): array
    {
        $validatedLevels = self::DEFAULT_LEVELS;

        if ($customLevels !== null) {
            foreach ($customLevels as $customLevel) {
                $sanitizedLevel = $security->sanitize(['level' => $customLevel])['level'] ?? '';
                $validatedLevels[] = $security->validateLevel($sanitizedLevel, [$sanitizedLevel]);
            }
        }

        return array_values(array_unique($validatedLevels));
    }
}
