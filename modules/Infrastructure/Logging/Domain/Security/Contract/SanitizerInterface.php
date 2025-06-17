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
     * Sanitizes sensitive keys and values from the provided input array.
     *
     * Returns a sanitized copy where all confidential data is masked, removed,
     * or replaced according to the policy of the domain.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed> Sanitized array safe for logging or export.
     */
    public function sanitize(array $input): array;
}
