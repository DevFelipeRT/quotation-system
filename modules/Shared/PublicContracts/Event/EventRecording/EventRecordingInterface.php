<?php

declare(strict_types=1);

namespace PublicContracts\Event\EventRecording;

/**
 * Interface for components that record domain or infrastructure events.
 *
 * This interface defines the public contract for managing recorded events,
 * allowing external dispatchers or handlers to interact with them.
 */
interface EventRecordingInterface
{
    /**
     * Releases all recorded events and clears the internal buffer.
     *
     * @return object[] The list of recorded events in order of emission.
     */
    public function releaseEvents(): array;

    /**
     * Peeks the currently recorded events without clearing them.
     *
     * @return object[] The list of recorded events.
     */
    public function peekEvents(): array;

    /**
     * Indicates whether any events have been recorded.
     *
     * @return bool True if there are pending events; false otherwise.
     */
    public function hasRecordedEvents(): bool;

    /**
     * Clears all recorded events without returning them.
     */
    public function clearRecordedEvents(): void;
}