<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Contract;

/**
 * CircularReferenceDetectorInterface
 *
 * Defines the contract for detecting circular references during recursive sanitization.
 * Responsible for tracking and identifying already-visited arrays and objects
 * within a single sanitization cycle.
 */
interface CircularReferenceDetectorInterface
{
    /**
     * Resets the internal tracking state.
     * Must be called at the root level of recursion to ensure
     * that previous detection state does not leak between cycles.
     *
     * @return void
     */
    public function reset(): void;

    /**
     * Determines if the given data (array or object) has already been seen
     * during the current recursion cycle (circular reference detection).
     *
     * @param mixed $data The data to check for circular references (array or object).
     * @return bool True if a circular reference is detected, false otherwise.
     */
    public function isCircularReference(mixed &$data): bool;

    /**
     * Registers the given data (array or object) as visited
     * within the current recursion cycle.
     *
     * @param mixed $data The data to register (array or object).
     * @return void
     */
    public function markSeen(mixed &$data): void;

    /**
     * Handles detected circular references, typically returning
     * a marker value that can be inserted into the sanitized output.
     *
     * @return array Marker value for a detected circular reference.
     */
    public function handleCircularReference(): array;
}
