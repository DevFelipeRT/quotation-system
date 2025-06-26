<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Tools;

use Logging\Domain\Exception\InvalidSanitizationConfigException;

/**
 * MaskTokenValidator
 *
 * responsible for validating and normalizing mask tokens used
 * to replace sensitive data. Ensures compliance with security policies,
 * including forbidden patterns and length constraints.
 */
final class MaskTokenValidator
{
    private const DEFAULT_MAX_LENGTH = 40;
    private const DEFAULT_FORBIDDEN_PATTERN = '/[\\x00-\\x1F\\x7F]|base64|script|php/i';

    private string $forbiddenPattern;
    private int $maxLength;

    /**
     * @param string|null $forbiddenPattern
     * @param int|null $maxLength
     */
    public function __construct(?string $forbiddenPattern = null, ?int $maxLength = null)
    {
        $this->forbiddenPattern = $forbiddenPattern ?? self::DEFAULT_FORBIDDEN_PATTERN;
        $this->maxLength = $maxLength ?? self::DEFAULT_MAX_LENGTH;
    }

    /**
     * Validates and normalizes the provided mask token.
     * - Trims whitespace, unwraps brackets if present, uppercases, and rewraps in brackets.
     * - Ensures token is not empty, does not exceed max length, and does not match forbidden patterns.
     *
     * @param string $maskToken
     * @return string
     * @throws InvalidSanitizationConfigException
     */
    public function validate(string $maskToken): string
    {
        $clean = trim($maskToken);

        if ($clean === '' || mb_strlen($clean) > $this->maxLength || preg_match($this->forbiddenPattern, $clean)) {
            throw InvalidSanitizationConfigException::forMaskToken($maskToken);
        }

        // Normalize: always [UPPERCASE]
        $unwrapped = preg_replace('/^\\[([^\\[\\]]*)\\]$/', '$1', $clean);
        $unwrapped = str_replace(['[', ']'], '', $unwrapped);
        $final = '[' . mb_strtoupper($unwrapped) . ']';
        return $final;
    }
}
