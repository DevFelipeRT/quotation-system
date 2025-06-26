<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Detector;

use Logging\Security\Sanitizing\Contract\CircularReferenceDetectorInterface;
use SplObjectStorage;
use stdClass;

/**
 * CircularReferenceDetector
 *
 * Tracks and detects circular references during recursive operations by checking
 * for true variable identity. This implementation is robust against false positives.
 *
 * - For objects, it uses SplObjectStorage for efficient and accurate identity tracking.
 * - For arrays, it uses a temporary key modification technique to reliably verify
 * if two variables point to the exact same array in memory.
 */
final class CircularReferenceDetector implements CircularReferenceDetectorInterface
{
    /**
     * @var SplObjectStorage Tracks all visited objects.
     */
    private SplObjectStorage $seenObjects;

    /**
     * @var array<int, array> Stores references to all visited arrays.
     */
    private array $seenArrays = [];

    public function __construct()
    {
        $this->seenObjects = new SplObjectStorage();
    }

    /**
     * @inheritdoc
     */
    public function reset(): void
    {
        $this->seenObjects = new SplObjectStorage();
        $this->seenArrays = [];
    }
    
    /**
     * @inheritdoc
     */
    public function isCircularReference(mixed &$data): bool
    {
        if (is_array($data)) {
            return $this->isCircularArray($data);
        }

        if (is_object($data)) {
            return $this->seenObjects->contains($data);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function markSeen(mixed &$data): void
    {
        if (is_array($data)) {
            // Store a direct reference to the array.
            $this->seenArrays[] = &$data;
        } elseif (is_object($data)) {
            $this->seenObjects->attach($data);
        }
    }

    /**
     * Detects if the given array has already been visited by reference.
     *
     * It iterates through already seen arrays and uses a temporary, unique marker
     * to check if the current array is a reference to any of them.
     *
     * @param array &$array The array to check.
     * @return bool True if a circular reference is detected, false otherwise.
     */
    private function isCircularArray(array &$array): bool
    {
        foreach ($this->seenArrays as &$seenArray) {
            // Use a robust method to check if $array and $seenArray are
            // references to the exact same data structure.
            if ($this->isSameArrayInstance($array, $seenArray)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if two array variables are references to the exact same instance.
     *
     * Standard comparison '===' checks for identical key/value pairs, not identity.
     * This method adds a unique marker to the first array and checks for its
     * presence in the second to confirm they are the same instance.
     *
     * @param array &$a The first array.
     * @param array &$b The second array.
     * @return bool
     */
    private function isSameArrayInstance(array &$a, array &$b): bool
    {
        // A highly unique key that is extremely unlikely to exist in the source array.
        $key = '__CIRCULAR_REF_CHECK_' . spl_object_hash(new stdClass()) . '__';

        // Add the temporary marker to the first array.
        $a[$key] = true;

        // Check if the marker now exists in the second array.
        // This will only be true if $a and $b point to the same memory location.
        $isSame = isset($b[$key]);

        // IMPORTANT: Clean up by removing the temporary key.
        unset($a[$key]);

        return $isSame;
    }

    /**
     * @inheritdoc
     */
    public function handleCircularReference(): array
    {
        return ['[CIRCULAR_REFERENCE_DETECTED]' => true];
    }
}