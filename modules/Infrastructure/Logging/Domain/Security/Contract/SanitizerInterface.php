<?php

declare(strict_types=1);

namespace Logging\Domain\Security\Contract;

/**
 * Contract for sanitization of input data within the logging domain.
 *
 * Implementations MUST ensure that all sensitive or confidential information
 * is masked or removed from arrays or values before any data is persisted,
 * transmitted, or exposed externally.
 *
 * Typical responsibilities include masking sensitive keys (e.g., passwords, tokens),
 * stripping forbidden values, and normalizing safe content for logging.
 */
interface SanitizerInterface
{
    /**
     * Sanitizes sensitive data from any input value.
     *
     * Returns a sanitized version of the input, masking or removing confidential data
     * according to the domain policy. Arrays and objects are sanitized recursively.
     * Strings são tratados individualmente; outros tipos escalares são retornados sem alteração.
     *
     * @param mixed $input
     * @param string|null $maskToken Optional custom mask token; if null, uses the default.
     * @return mixed Sanitized value, of the same type as input.
     */
    public function sanitize(mixed $input, ?string $maskToken = null): mixed;

    /**
     * Determines whether a value, any of its keys, or any of its nested values/keys
     * are considered sensitive according to the sanitizer's security policy.
     *
     * This method recursively evaluates arrays and objects, inspecting both their keys
     * and values. If any key or value is identified as sensitive—either by pattern match
     * or by sensitive-key detection—the method returns true.
     *
     * @param mixed $value Input to be analyzed (string, array, object, or scalar).
     * @return bool True if any key or value (recursively) is considered sensitive; otherwise, false.
     */
    public function isSensitive(mixed $value): bool;
}
