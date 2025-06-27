<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Service;

use Logging\Security\Sanitizing\Contract\ArraySanitizerInterface;
use Logging\Security\Sanitizing\Contract\CircularReferenceDetectorInterface;
use Logging\Security\Sanitizing\Contract\ObjectSanitizerInterface;
use Logging\Security\Sanitizing\Contract\SensitiveKeyDetectorInterface;
use Logging\Security\Sanitizing\Contract\StringSanitizerInterface;

/**
 * ArraySanitizer
 *
 * Recursively sanitizes arrays for logging and security purposes. This class provides
 * a robust mechanism to clean data structures before they are logged or stored,
 * preventing both data leakage and application errors.
 *
 * Key features include:
 * - Masking values for keys identified as sensitive.
 * - Delegating string sanitization (e.g., partial masking) to a dedicated StringSanitizer.
 * - Delegating object sanitization to a dedicated ObjectSanitizer.
 * - Robustly handling circular references in both arrays and objects to prevent infinite loops.
 * - Enforcing a maximum recursion depth as a fail-safe.
 */
final class ArraySanitizer implements ArraySanitizerInterface
{
    private StringSanitizerInterface $stringSanitizer;
    private SensitiveKeyDetectorInterface $keyDetector;
    private ObjectSanitizerInterface $objectSanitizer;
    private CircularReferenceDetectorInterface $circularReferenceDetector;
    private string $maskToken;
    private int $maxDepth;

    /**
     * Constructs the ArraySanitizer.
     *
     * @param StringSanitizerInterface           $stringSanitizer           Performs partial masking of string values.
     * @param SensitiveKeyDetectorInterface      $keyDetector               Detects keys that require full value masking.
     * @param ObjectSanitizerInterface           $objectSanitizer           Performs sanitization on object values.
     * @param CircularReferenceDetectorInterface $circularReferenceDetector Detects and handles circular references.
     * @param string                             $maskToken                 The string to use for masking sensitive data.
     * @param int                                $maxDepth                  The maximum recursion depth to traverse.
     */
    public function __construct(
        StringSanitizerInterface $stringSanitizer,
        SensitiveKeyDetectorInterface $keyDetector,
        ObjectSanitizerInterface $objectSanitizer,
        CircularReferenceDetectorInterface $circularReferenceDetector,
        string $maskToken,
        int $maxDepth
    ) {
        $this->stringSanitizer = $stringSanitizer;
        $this->keyDetector = $keyDetector;
        $this->objectSanitizer = $objectSanitizer;
        $this->circularReferenceDetector = $circularReferenceDetector;
        $this->maskToken = $maskToken;
        $this->maxDepth = $maxDepth;
    }

    /**
     * Sanitizes an array recursively.
     *
     * This is the main public entry point for sanitization. It initializes the
     * circular reference detector for a new run and starts the recursive process.
     *
     * @param array $array The array to sanitize.
     * @return array The fully sanitized array.
     */
    public function sanitizeArray(array $array): array
    {
        // Reset the detector to ensure a clean slate for each top-level sanitization call.
        $this->circularReferenceDetector->reset();

        return $this->sanitizeArrayRecursive($array, 0);
    }

    /**
     * The internal recursive routine for sanitizing an array.
     *
     * This method is the workhorse of the class. It checks for circular references
     * and recursion depth before iterating over the array elements. The array is
     * passed by reference to allow for accurate circular reference detection.
     *
     * @param array &$array The array to sanitize at the current level (passed by reference).
     * @param int   $depth  The current recursion depth.
     * @return array The sanitized array at the current level.
     */
    private function sanitizeArrayRecursive(array &$array, int $depth): array
    {
        // 1. Check for circular references first to prevent infinite loops.
        if ($this->circularReferenceDetector->isCircularReference($array)) {
            return $this->circularReferenceDetector->handleCircularReference();
        }

        // 2. Check for max depth as a secondary safeguard.
        if ($depth >= $this->maxDepth) {
            return ['[SANITIZATION_HALTED]' => 'MAX_DEPTH_REACHED'];
        }

        // 3. Mark this array as "seen" for future circular reference checks.
        $this->circularReferenceDetector->markSeen($array);

        $sanitized = [];
        // Iterate by reference to correctly handle nested structures.
        foreach ($array as $key => &$value) {
            $sanitized[$key] = $this->sanitizeElement($key, $value, $depth);
        }

        return $sanitized;
    }

    /**
     * Sanitizes a single element based on its type.
     *
     * This method acts as a dispatcher, determining how to handle a value based
     * on its key and type. It passes values by reference to support circular
     * reference detection on nested structures.
     *
     * @param mixed $key    The element's key.
     * @param mixed &$value The element's value (passed by reference).
     * @param int   $depth  The current recursion depth.
     * @return mixed The sanitized value.
     */
    private function sanitizeElement(mixed $key, mixed &$value, int $depth): mixed
    {
        if (is_string($key) && $this->keyDetector->isSensitiveKey($key)) {
            return $this->maskToken;
        }

        if (is_array($value)) {
            // Recurse for nested arrays. Circular checks are handled within the called method.
            return $this->sanitizeArrayRecursive($value, $depth + 1);
        }

        if (is_object($value)) {
            // Circular checks are handled within the called method.
            return $this->objectSanitizer->sanitizeObject($value);
        }

        if (is_string($value)) {
            return $this->stringSanitizer->sanitizeString($value, $this->maskToken);
        }

        // For all other scalar types (int, float, bool, null), return the value as is.
        return $value;
    }
}