<?php

declare(strict_types=1);

namespace PublicContracts\Logging;

/**
 * Contract for sanitization configuration in the logging domain.
 *
 * Provides all externally configurable values, lists, and patterns
 * required for secure, robust, and domain-specific sanitization routines.
 */
interface SanitizationConfigInterface
{
    /**
     * Returns the list of sensitive keys whose values should be masked.
     *
     * @return string[]
     */
    public function sensitiveKeys(): array;

    /**
     * Returns the list of regular expression patterns for matching sensitive values.
     *
     * @return string[]
     */
    public function sensitivePatterns(): array;

    /**
     * Returns the maximum recursion depth for nested sanitization.
     *
     * @return int
     */
    public function maxDepth(): int;

    /**
     * Returns the mask token to be used in place of sensitive values.
     *
     * @return string
     */
    public function maskToken(): string;

    /**
     * Returns the regular expression pattern for forbidden characters or strings
     * in the mask token.
     *
     * @return string
     */
    public function maskTokenForbiddenPattern(): string;
}
