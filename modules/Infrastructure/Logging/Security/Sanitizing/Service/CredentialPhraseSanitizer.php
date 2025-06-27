<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Service;

use Logging\Domain\Exception\InvalidSanitizationConfigException;
use Logging\Security\Sanitizing\Contract\SensitiveKeyDetectorInterface;

/**
 * CredentialPhraseSanitizer
 *
 * Service responsible for detecting and masking sensitive credential phrases in strings.
 * Supports both forward (key → separator → value) and backward (value ← separator ← key) masking.
 */
final class CredentialPhraseSanitizer
{
    private const DEFAULT_SEPARATORS = [
        ':', '=', '-', '->', '=>', '|', '/', ';', ',', 'is', 'foi', 'é'
    ];

    private const MAX_INTERMEDIATE_WORDS = 3;

    private const FORWARD_PATTERN_TEMPLATE =
        '/\b(%s)\b'                      // [1] Sensitive key
        . '((?:\s+\w+){0,%d})'           // [2] Optional intermediate words
        . '(\s*)'                        // [3] Spaces before separator
        . '(%s)'                         // [4] Separator(s)
        . '(\s*)'                        // [5] Spaces after separator
        . '([^\s,;:."]+)/iu';            // [6] Value

    private const BACKWARD_PATTERN_TEMPLATE =
        '/([^\s,;:."]+)'                 // [1] Value to be masked
        . '(\s+)'                        // [2] Spaces after value (required)
        . '(%s)'                         // [3] Separator(s)
        . '(\s*(?:\w+\s*){0,%d})'        // [4] Optional intermediates (with spaces)
        . '(\s+)'                        // [5] Spaces before key (required)
        . '(\b%s\b)/iu';                 // [6] Sensitive key

    private SensitiveKeyDetectorInterface $sensitiveKeyDetector;
    private array $separatorList;

    public function __construct(
        SensitiveKeyDetectorInterface $sensitiveKeyDetector,
        ?array $customSeparators = null
    ) {
        $this->sensitiveKeyDetector = $sensitiveKeyDetector;
        $this->separatorList = self::initializeSeparatorList($customSeparators);
    }

    /**
     * Masks sensitive credential values in a phrase.
     * Applies forward masking first. If no match, applies backward masking.
     *
     * @param string $text Input string to process.
     * @param string $maskToken Masking token to use (e.g., '[MASKED]').
     * @return string The sanitized string with sensitive values masked.
     */
    public function sanitizePhrase(string $text, string $maskToken): string
    {
        $preparedSensitiveKeys = $this->sensitiveKeyDetector->getPreparedKeys();
        if (empty($preparedSensitiveKeys)) {
            return $text;
        }

        $replacementCount = 0;
        $sanitized = $this->maskForwardPhrase(
            $text,
            $maskToken,
            $preparedSensitiveKeys,
            $replacementCount
        );

        if ($replacementCount > 0) {
            return $sanitized;
        }
        return $this->maskBackwardPhrase(
            $text,
            $maskToken,
            $preparedSensitiveKeys
        );
    }

    /**
     * Applies forward masking: key [optionals] separator value.
     *
     * @param string   $text
     * @param string   $maskToken
     * @param string[] $sensitiveKeys
     * @param int|null $replacementCount
     * @return string
     */
    private function maskForwardPhrase(
        string $text,
        string $maskToken,
        array $sensitiveKeys,
        ?int &$replacementCount = null
    ): string {
        $pattern = $this->buildForwardPattern($sensitiveKeys);

        return preg_replace_callback(
            $pattern,
            fn($match) => $this->replaceForwardMatch($match, $maskToken, $replacementCount),
            $text,
            -1,
            $replacementCount
        );
    }

    /**
     * Applies backward masking: value separator [optionals] key.
     *
     * @param string   $text
     * @param string   $maskToken
     * @param string[] $sensitiveKeys
     * @return string
     */
    private function maskBackwardPhrase(
        string $text,
        string $maskToken,
        array $sensitiveKeys
    ): string {
        $pattern = $this->buildBackwardPattern($sensitiveKeys);

        return preg_replace_callback(
            $pattern,
            fn($match) => $this->replaceBackwardMatch($match, $maskToken),
            $text
        );
    }

    /**
     * Builds the regex for the forward pattern.
     *
     * @param string[] $sensitiveKeys
     * @return string
     */
    private function buildForwardPattern(array $sensitiveKeys): string
    {
        $separatorRegex = implode('|', array_map(
            static fn($sep) => preg_quote($sep, '/'), $this->separatorList
        ));
        $keysRegex = implode('|', array_map(
            static fn($key) => preg_quote($key, '/'), $sensitiveKeys
        ));
        return sprintf(
            self::FORWARD_PATTERN_TEMPLATE,
            $keysRegex,
            self::MAX_INTERMEDIATE_WORDS,
            $separatorRegex
        );
    }

    /**
     * Builds the regex for the backward pattern.
     *
     * @param string[] $sensitiveKeys
     * @return string
     */
    private function buildBackwardPattern(array $sensitiveKeys): string
    {
        $separatorRegex = implode('|', array_map(
            static fn($sep) => preg_quote($sep, '/'), $this->separatorList
        ));
        $keysRegex = implode('|', array_map(
            static fn($key) => preg_quote($key, '/'), $sensitiveKeys
        ));
        return sprintf(
            self::BACKWARD_PATTERN_TEMPLATE,
            $separatorRegex,
            self::MAX_INTERMEDIATE_WORDS,
            $keysRegex
        );
    }

    /**
     * Handles the replacement for forward matches.
     *
     * @param array $match
     * @param string $maskToken
     * @param int &$replacementCount
     * @return string
     */
    private function replaceForwardMatch(array $match, string $maskToken, ?int &$replacementCount = null): string
    {
        ++$replacementCount;
        // [1]=key, [2]=intermediates, [3]=spaces before sep, [4]=sep, [5]=spaces after sep, [6]=value
        return $match[1] . $match[2] . $match[3] . $match[4] . $match[5] . $maskToken;
    }

    /**
     * Handles the replacement for backward matches.
     *
     * @param array $match
     * @param string $maskToken
     * @return string
     */
    private function replaceBackwardMatch(array $match, string $maskToken): string
    {
        // [1]=value, [2]=spaces after value, [3]=sep, [4]=intermediates, [5]=spaces before key, [6]=key
        return $maskToken . $match[2] . $match[3] . $match[4] . $match[5] . $match[6];
    }

    /**
     * Initializes and validates the separator list.
     *
     * @param string[]|null $customSeparators
     * @return string[]
     */
    private static function initializeSeparatorList(?array $customSeparators): array
    {
        $validatedCustom = $customSeparators !== null
            ? self::validateSeparators($customSeparators)
            : [];
        return array_values(array_unique(array_merge($validatedCustom, self::DEFAULT_SEPARATORS)));
    }

    /**
     * Validates that all separators are non-empty strings and do not contain whitespace.
     *
     * @param array $separatorCandidates
     * @return array
     * @throws InvalidSanitizationConfigException
     */
    private static function validateSeparators(array $separatorCandidates): array
    {
        $validated = [];
        foreach ($separatorCandidates as $separator) {
            if (!is_string($separator) || trim($separator) === '' || preg_match('/\s/', $separator)) {
                throw InvalidSanitizationConfigException::forSeparator($separator);
            }
            $validated[] = $separator;
        }
        return array_values(array_unique($validated));
    }
}
