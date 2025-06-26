<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Contract;

/**
 * SensitiveKeyDetectorInterface
 *
 * Contract for classes responsible for detecting sensitive keys in the logging domain.
 * Designed for high-performance detection using Unicode normalization, case folding,
 * fuzzy matching, and optional custom key lists to accommodate multiple languages and formats.
 */
interface SensitiveKeyDetectorInterface
{
    /**
     * Determines whether the given key should be considered sensitive.
     * Detection may use Unicode normalization, case folding, fuzzy matching,
     * and optional key transforms.
     *
     * @param string $key The key to check for sensitivity.
     * @return bool True if the key is considered sensitive, false otherwise.
     */
    public function isSensitiveKey(string $key): bool;

    /**
     * Returns the list of prepared sensitive keys after all
     * normalization and transform strategies have been applied.
     * Useful for debugging, testing, or audit purposes.
     *
     * @return string[]
     */
    public function getPreparedKeys(): array;
}
