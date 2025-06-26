<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Contract;

/**
 * ObjectSanitizerInterface
 *
 * Defines a contract for services that sanitize objects by recursively traversing and sanitizing their properties.
 *
 * Implementations must:
 * - Convert object properties to associative arrays.
 * - Detect and handle circular references to prevent infinite recursion.
 * - Recursively sanitize nested arrays, objects, and strings according to domain security policy.
 *
 * This interface ensures that all object data is properly sanitized for secure logging and output, handling nested structures and circular references transparently.
 */
interface ObjectSanitizerInterface
{
    /**
     * Sanitizes an object by extracting and recursively sanitizing its properties.
     *
     * @param object $object The object to sanitize.
     * @return array         An associative array with all properties sanitized.
     */
    public function sanitizeObject(object $object): array;
}

