<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Contract;

/**
 * StringSanitizerInterface
 *
 * Contract for services responsible for sanitizing string values
 * by masking sensitive patterns and credential phrases for secure logging.
 */
interface StringSanitizerInterface
{
    /**
     * Sanitizes a string value by masking sensitive data according to
     * configured patterns and credential key phrases.
     *
     * @param string $value The string value to sanitize.
     * @param string $maskToken The token to use for masking sensitive data.
     *                          Must be bracketed, uppercase, safe, and up to 40 chars.
     * @return string The sanitized string.
     *
     * @throws \InvalidArgumentException If the mask token is invalid.
     */
    public function sanitizeString(string $value, string $maskToken): string;
}
