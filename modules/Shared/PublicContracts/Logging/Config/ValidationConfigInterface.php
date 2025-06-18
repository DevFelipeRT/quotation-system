<?php

declare(strict_types=1);

namespace PublicContracts\Logging\Config;

/**
 * Provides all configurable parameters for domain validation rules.
 *
 * Implementations supply limits, patterns, and strings used by Validator
 * to enforce the invariants of logging value objects.
 */
interface ValidationConfigInterface
{
    /**
     * Returns the default maximum length for generic string values.
     *
     * @return int
     */
    public function defaultStringMaxLength(): int;

    /**
     * Returns the regular expression that matches forbidden characters
     * in any domain string.
     *
     * @return string
     */
    public function stringForbiddenCharsRegex(): string;

    /**
     * Returns the maximum allowed length for context array keys.
     *
     * @return int
     */
    public function contextKeyMaxLength(): int;

    /**
     * Returns the maximum allowed length for context array values.
     *
     * @return int
     */
    public function contextValueMaxLength(): int;

    /**
     * Returns the string that represents the root directory,
     * used to disallow root paths in validateDirectory.
     *
     * @return string
     */
    public function directoryRootString(): string;

    /**
     * Returns the substring used to detect directory traversal sequences,
     * e.g. "..".
     *
     * @return string
     */
    public function directoryTraversalString(): string;

    /**
     * Returns the maximum allowed length for log messages.
     *
     * @return int
     */
    public function logMessageMaxLength(): int;

    /**
     * Returns the regular expression that matches terminal punctuation
     * (e.g. period, exclamation, question mark) used to validate that
     * log messages end properly.
     *
     * @return string
     */
    public function logMessageTerminalPunctuationRegex(): string;
}
