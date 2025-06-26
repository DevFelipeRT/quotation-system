<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Service;

use Logging\Security\Sanitizing\Contract\SensitivePatternDetectorInterface;
use Logging\Security\Sanitizing\Tools\UnicodeNormalizer;

/**
 * SensitivePatternSanitizer
 *
 * Responsible for masking sensitive patterns in strings using regular expressions.
 * Designed for high-performance partial sanitization of any configured pattern,
 * ensuring flexible and extensible matching for PII, credentials, and custom data signatures.
 */
final class SensitivePatternSanitizer
{
    /**
     * @var SensitivePatternDetectorInterface
     */
    private SensitivePatternDetectorInterface $patternDetector;

    /**
     * @var UnicodeNormalizer
     */
    private UnicodeNormalizer $unicodeNormalizer;

    /**
     * @param SensitivePatternDetectorInterface $patternDetector
     * @param UnicodeNormalizer                 $unicodeNormalizer
     */
    public function __construct(
        SensitivePatternDetectorInterface $patternDetector,
        UnicodeNormalizer $unicodeNormalizer
    ) {
        $this->patternDetector = $patternDetector;
        $this->unicodeNormalizer = $unicodeNormalizer;
    }

    /**
     * Masks all matches of configured sensitive patterns with the mask token.
     * Responsible only for orchestrating the normalization, pattern extraction, and replacement.
     *
     * @param string $value
     * @param string $maskToken
     * @return string
     */
    public function sanitizePatterns(string $value, string $maskToken): string
    {
        $normalized = $this->normalizeString($value);

        $patterns = $this->getCleanPatterns();
        if (empty($patterns)) {
            return $normalized;
        }

        $regex = $this->buildUnifiedRegex($patterns);

        return $this->replacePatternsWithMask($normalized, $regex, $maskToken);
    }

    /**
     * Normalizes a string (Unicode normalization and trimming).
     *
     * @param string $value
     * @return string
     */
    private function normalizeString(string $value): string
    {
        return trim($this->unicodeNormalizer->normalize($value));
    }

    /**
     * Retrieves and cleans patterns, removing regex delimiters and flags.
     *
     * @return string[]
     */
    private function getCleanPatterns(): array
    {
        $patterns = $this->patternDetector->getPatterns();

        return array_map(function ($pattern) {
            if ($pattern !== '' && $pattern[0] === '/') {
                $lastSlash = strrpos($pattern, '/');
                if ($lastSlash !== 0) {
                    // Extract inner pattern only, ignore regex flags
                    return substr($pattern, 1, $lastSlash - 1);
                }
            }
            return $pattern;
        }, $patterns);
    }

    /**
     * Builds a unified regex pattern from an array of patterns.
     *
     * @param string[] $patterns
     * @return string
     */
    private function buildUnifiedRegex(array $patterns): string
    {
        return '/' . implode('|', $patterns) . '/iu';
    }

    /**
     * Replaces all matches of the provided regex in the input string with the mask token.
     *
     * @param string $input
     * @param string $regex
     * @param string $maskToken
     * @return string
     */
    private function replacePatternsWithMask(string $input, string $regex, string $maskToken): string
    {
        return preg_replace_callback(
            $regex,
            static function () use ($maskToken) {
                return $maskToken;
            },
            $input
        );
    }
}
