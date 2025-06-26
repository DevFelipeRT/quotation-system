<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Contract;

/**
 * ArraySanitizerInterface
 *
 * Contract for services that recursively sanitize arrays for secure logging and data handling.
 *
 * Implementations must:
 * - Mask values for sensitive keys using a configured detector.
 * - Sanitize string values via StringSanitizerInterface (partial masking).
 * - Sanitize object values via ObjectSanitizerInterface (deep recursive sanitization).
 * - Preserve non-sensitive values and structure whenever possible.
 * - Enforce a maximum recursion depth, after which deeply nested data is masked.
 *
 * This interface does not define how keys are determined to be sensitive, or how
 * strings and objects are sanitized; these concerns are delegated to the respective injected services.
 */
interface ArraySanitizerInterface
{
    /**
     * Recursively sanitizes an array for secure logging or output.
     *
     * Sensitive key values are masked, string values are sanitized,
     * and object values are sanitized via ObjectSanitizerInterface.
     * Recursion depth is enforced according to the implementation.
     *
     * @param array $array The input array to sanitize (may contain nested arrays or objects).
     * @return array       The sanitized array with all sensitive data masked as per policy.
     */
    public function sanitizeArray(array $array): array;
}

