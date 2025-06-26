<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Detector;

use Logging\Domain\Exception\InvalidSanitizationConfigException;
use Logging\Security\Sanitizing\Contract\SensitivePatternDetectorInterface;

/**
 * SensitivePatternDetector
 *
 * Responsible for detecting sensitive values using regular expressions.
 * Merges a default set of sensitive value patterns (e.g., for CPF, credit card, email) with
 * any custom patterns provided at runtime. Designed for high-throughput logging scenarios.
 *
 * - All patterns are validated for syntax at construction.
 * - Detection applies all registered patterns in order.
 * - Can be extended with project-specific or locale-specific patterns.
 */
final class SensitivePatternDetector implements SensitivePatternDetectorInterface
{
    /**
     * Internal default list of regular expressions for sensitive data detection.
     * This list is merged with any custom patterns provided at runtime.
     */
    private const DEFAULT_SENSITIVE_PATTERNS = [
        '/\\b\\d{3}\\.?\\d{3}\\.?\\d{3}-?\\d{2}\\b/',      // CPF
        '/\\b\\d{16}\\b/',                                 // Credit card (16 digits)
        '/[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,}/i',       // Email
    ];

    /**
     * All compiled regular expressions for sensitive value detection.
     *
     * @var string[]
     */
    private array $patterns;

    /**
     * Constructs a new SensitivePatternDetector with optional custom patterns.
     *
     * @param string[] $customPatterns Additional regular expressions for sensitive data.
     * @throws \InvalidArgumentException If any custom pattern is not a valid regex.
     */
    public function __construct(array $customPatterns = [])
    {
        $merged = array_merge(self::DEFAULT_SENSITIVE_PATTERNS, $customPatterns);
        $this->assertValidPatterns($merged);
        $this->patterns = $merged;
    }

    /**
     * Determines whether the given value matches any configured sensitive data pattern.
     * Only string values are tested; non-string values are always considered non-sensitive.
     *
     * @param mixed $value
     * @return bool True if any pattern matches; false otherwise.
     */
    public function matchesSensitivePatterns(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns all active sensitive patterns (for debugging or audit).
     *
     * @return string[]
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * Validates that all provided regular expression patterns are valid.
     * Throws an exception if any pattern is invalid.
     *
     * @param string[] $patterns List of regex patterns to validate.
     * @throws \InvalidArgumentException If any pattern is not a valid regular expression.
     */
    private function assertValidPatterns(array $patterns): void
    {
        foreach ($patterns as $pattern) {
            if (@preg_match($pattern, '') === false) {
                throw InvalidSanitizationConfigException::forPattern($pattern);
            }
        }
    }
}
