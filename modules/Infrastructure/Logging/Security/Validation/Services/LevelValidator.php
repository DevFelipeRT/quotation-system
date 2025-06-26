<?php

declare(strict_types=1);

namespace Logging\Security\Validation\Services;

use Logging\Security\Validation\Tools\StringValidationTrait;
use Logging\Domain\Exception\InvalidLogLevelException;

/**
 * LevelValidator
 *
 * Validates log level strings, ensuring they are non-empty and belong to the set of allowed levels.
 * Normalizes the level to lowercase. Throws a domain-specific exception on failure.
 */
final class LevelValidator
{
    use StringValidationTrait;

    /**
     * Validates a log level.
     *
     * Ensures the provided level is not empty and matches one of the allowed levels.
     * Normalizes the level to lowercase before comparison and return.
     *
     * @param string   $level         The log level to validate.
     * @param string[] $allowedLevels Set of allowed log levels (lowercase).
     *
     * @return string                 Validated and normalized log level.
     *
     * @throws InvalidLogLevelException If the level is empty or not allowed.
     */
    public function validate(string $level, array $allowedLevels): string
    {
        $normalized = mb_strtolower($this->cleanString($level, true));

        if ($this->isEmpty($normalized)) {
            throw InvalidLogLevelException::forLevel($level);
        }
        if (!in_array($normalized, $allowedLevels, true)) {
            throw InvalidLogLevelException::forLevel($level);
        }

        return $normalized;
    }
}
