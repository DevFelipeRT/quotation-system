<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Service;

use Logging\Security\Sanitizing\Contract\ObjectSanitizerInterface;
use Logging\Security\Sanitizing\Contract\CircularReferenceDetectorInterface;
use Logging\Security\Sanitizing\Contract\SensitiveKeyDetectorInterface;
use Logging\Security\Sanitizing\Contract\StringSanitizerInterface;

/**
 * ObjectSanitizer
 *
 * Recursively sanitizes objects for logging or security purposes.
 * Masks sensitive properties using SensitiveKeyDetectorInterface,
 * sanitizes strings using StringSanitizerInterface, and enforces both
 * recursion depth limits and circular reference detection.
 */
final class ObjectSanitizer implements ObjectSanitizerInterface
{
    private CircularReferenceDetectorInterface $circularDetector;
    private SensitiveKeyDetectorInterface $keyDetector;
    private StringSanitizerInterface $stringSanitizer;
    private string $maskToken;
    private int $maxDepth;

    /**
     * @param CircularReferenceDetectorInterface $circularDetector    Detects circular references to prevent infinite loops.
     * @param SensitiveKeyDetectorInterface     $keyDetector         Detects property names requiring masking.
     * @param StringSanitizerInterface          $stringSanitizer     Performs string-level sanitization (partial masking).
     * @param string                            $maskToken           Token used for masking sensitive data.
     * @param int                               $maxDepth            Maximum recursion depth permitted.
     */
    public function __construct(
        CircularReferenceDetectorInterface $circularDetector,
        SensitiveKeyDetectorInterface $keyDetector,
        StringSanitizerInterface $stringSanitizer,
        string $maskToken,
        int $maxDepth
    ) {
        $this->circularDetector = $circularDetector;
        $this->keyDetector = $keyDetector;
        $this->stringSanitizer = $stringSanitizer;
        $this->maskToken = $maskToken;
        $this->maxDepth = $maxDepth;
    }

    /**
     * Public entry point: Recursively sanitizes an object for logging or security.
     *
     * All circular references are handled and the maximum recursion depth is enforced.
     *
     * @param object $object The object to sanitize.
     * @return array         Sanitized associative array representation of the object.
     */
    public function sanitizeObject(object $object): array
    {
        $this->circularDetector->reset();
        return $this->sanitizeObjectRecursive($object, 0);
    }

    /**
     * Recursively sanitizes an object and its properties for logging or security purposes.
     *
     * Masks sensitive properties, applies string sanitization, and handles arrays and nested objects.
     * Enforces maximum recursion depth and prevents infinite loops via circular reference detection.
     *
     * @param object $object The object to sanitize at the current recursion level.
     * @param int    $depth  The current recursion depth.
     * @return array         Sanitized associative array of the object's properties.
     */
    private function sanitizeObjectRecursive(object $object, int $depth): array
    {
        if ($depth >= $this->maxDepth) {
            return [$this->maskToken];
        }
        if ($this->circularDetector->isCircularReference($object)) {
            return $this->circularDetector->handleCircularReference();
        }
        $this->circularDetector->markSeen($object);

        $properties = get_object_vars($object);
        $sanitized = [];

        foreach ($properties as $property => $value) {
            $sanitized[$property] = $this->sanitizeElement($property, $value, $depth);
        }

        return $sanitized;
    }

    /**
     * Sanitizes a single property value by its name and value, according to the domain security policy.
     *
     * - Sensitive property names are masked entirely.
     * - Nested arrays and objects are recursively sanitized.
     * - String values are sanitized using StringSanitizerInterface.
     * - All other types are returned as is.
     *
     * @param string|int $property The property name.
     * @param mixed      $value    The property value.
     * @param int        $depth    The current recursion depth.
     * @return mixed               The sanitized value.
     */
    private function sanitizeElement($property, $value, int $depth): mixed
    {
        if (is_string($property) && $this->keyDetector->isSensitiveKey($property)) {
            return $this->maskToken;
        }
        if (is_array($value)) {
            return $this->sanitizeArrayRecursive($value, $depth + 1);
        }
        if (is_object($value)) {
            return $this->sanitizeObjectRecursive($value, $depth + 1);
        }
        if (is_string($value)) {
            return $this->stringSanitizer->sanitizeString($value, $this->maskToken);
        }
        return $value;
    }

    /**
     * Recursively sanitizes an array for use in object property traversal.
     *
     * Applies the same rules as object sanitization: depth limit, masking of sensitive keys,
     * and delegation of string sanitization.
     *
     * @param array $array The array to sanitize at the current recursion level.
     * @param int   $depth The current recursion depth.
     * @return array       The sanitized array.
     */
    private function sanitizeArrayRecursive(array $array, int $depth): array
    {
        if ($depth >= $this->maxDepth) {
            return [$this->maskToken];
        }

        $sanitized = [];
        foreach ($array as $key => $value) {
            if (is_string($key) && $this->keyDetector->isSensitiveKey($key)) {
                $sanitized[$key] = $this->maskToken;
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArrayRecursive($value, $depth + 1);
            } elseif (is_object($value)) {
                $sanitized[$key] = $this->sanitizeObjectRecursive($value, $depth + 1);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->stringSanitizer->sanitizeString($value, $this->maskToken);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
