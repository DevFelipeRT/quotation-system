<?php

declare(strict_types=1);

namespace Logging\Security\Validation\Tools;
/**
 * StringValidationTrait
 *
 * Provides reusable string validation logic for domain value objects and services.
 * Methods never throw exceptions, but return boolean results or normalized values.
 * The consuming class is responsible for control flow and exception handling.
 */
trait StringValidationTrait
{
    /**
     * Sanitizes a string by trimming or removing all whitespace.
     *
     * @param string $value
     * @param bool   $allowWhitespace
     * @return string
     */
    protected function cleanString(string $value, bool $allowWhitespace = true): string
    {
        return $allowWhitespace
            ? trim($value)
            : preg_replace('/\s+/', '', $value);
    }

    /**
     * Checks if the string is empty.
     *
     * @param string $value
     * @return bool
     */
    protected function isEmpty(string $value): bool
    {
        return $value === '';
    }

    /**
     * Checks if the string contains forbidden characters.
     *
     * @param string $value
     * @param string $forbiddenRegex
     * @return bool
     */
    protected function hasForbiddenChars(string $value, string $forbiddenRegex): bool
    {
        return $forbiddenRegex !== '' && preg_match($forbiddenRegex, $value);
    }

    /**
     * Checks if the string exceeds the maximum length.
     *
     * @param string $value
     * @param int    $max
     * @return bool
     */
    protected function exceedsMaxLength(string $value, int $max): bool
    {
        return $max > 0 && mb_strlen($value) > $max;
    }
}
