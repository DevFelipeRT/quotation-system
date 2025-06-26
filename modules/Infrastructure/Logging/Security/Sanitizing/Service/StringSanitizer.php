<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Service;

use Logging\Security\Sanitizing\Contract\StringSanitizerInterface;
use Logging\Security\Sanitizing\Tools\UnicodeNormalizer;

/**
 * StringSanitizer
 *
 * Provides partial sanitization for strings, masking only sensitive fragments and credential values.
 * The mask token is used exactly as received.
 */
final class StringSanitizer implements StringSanitizerInterface
{
    private SensitivePatternSanitizer $patternSanitizer;
    private CredentialPhraseSanitizer $phraseSanitizer;
    private UnicodeNormalizer         $unicodeNormalizer;

    /**
     * @param SensitivePatternSanitizer $patternSanitizer
     * @param CredentialPhraseSanitizer $phraseSanitizer
     * @param UnicodeNormalizer         $unicodeNormalizer
     */
    public function __construct(
        SensitivePatternSanitizer $patternSanitizer,
        CredentialPhraseSanitizer $phraseSanitizer,
        UnicodeNormalizer         $unicodeNormalizer
    ) {
        $this->patternSanitizer  = $patternSanitizer;
        $this->phraseSanitizer   = $phraseSanitizer;
        $this->unicodeNormalizer = $unicodeNormalizer;
    }

    /**
     * Partially sanitizes a string by masking sensitive fragments and credential values.
     *
     * @param string  $value
     * @param string  $maskToken
     * @return string
     */
    public function sanitizeString(string $value, string $maskToken): string
    {
        $normalized = $this->normalizeString($value);

        // Advanced credential phrase masking (sentences, complex cases)
        $sanitized = $this->phraseSanitizer->sanitizePhrase($normalized, $maskToken);

        // Generic pattern-based sensitive data masking
        $sanitized = $this->patternSanitizer->sanitizePatterns($sanitized, $maskToken);

        return $sanitized;
    }

    /**
     * Unicode normalization and whitespace trimming.
     *
     * @param string $value
     * @return string
     */
    private function normalizeString(string $value): string
    {
        return trim($this->unicodeNormalizer->normalize($value));
    }
}
