<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Contract;

/**
 * SensitivePatternDetectorInterface
 *
 * Contract for classes responsible for detecting sensitive values
 * using regular expressions in the logging domain.
 * Supports registration of custom patterns and provides
 * an interface for pattern-based sensitive value detection.
 */
interface SensitivePatternDetectorInterface
{
    /**
     * Determines whether the given value matches any configured sensitive data pattern.
     * Only string values are tested; non-string values are always considered non-sensitive.
     *
     * @param mixed $value The value to be checked against sensitive patterns.
     * @return bool True if the value matches any sensitive pattern; false otherwise.
     */
    public function matchesSensitivePatterns(mixed $value): bool;

    /**
     * Returns all active sensitive patterns used for detection.
     * Useful for debugging or auditing purposes.
     *
     * @return string[]
     */
    public function getPatterns(): array;
}
