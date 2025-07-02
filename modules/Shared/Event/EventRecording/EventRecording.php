<?php

declare(strict_types=1);

namespace Event\EventRecording;

/**
 * Trait for recording domain or infrastructure events
 * without dispatching them immediately.
 *
 * This approach allows events to be collected during a
 * transaction or unit of work, and dispatched explicitly
 * by an external dispatcher after successful operation.
 *
 * Typical usage:
 * - Inside entities, aggregates, services or connections
 * - Enables eventual consistency and traceable event flow
 * 
 * This trait should follow the public contract EventRecordingInterface
 *
 * @internal This trait should be used only in components that are lifecycle-managed externally.
 */
trait EventRecording
{
    /**
     * @var object[] Recorded events awaiting dispatch.
     */
    private array $recordedEvents = [];

    /**
     * Records a new event instance for later dispatch.
     *
     * @param object $event Any event object (domain or infrastructure-level)
     */
    protected function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /**
     * Releases all recorded events and clears the internal buffer.
     *
     * @return object[] The list of recorded events in order of emission.
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    /**
     * Peeks the currently recorded events without clearing them.
     *
     * @return object[] The list of recorded events.
     */
    public function peekEvents(): array
    {
        return $this->recordedEvents;
    }

    /**
     * Indicates whether any events have been recorded.
     *
     * @return bool True if there are pending events; false otherwise.
     */
    public function hasRecordedEvents(): bool
    {
        return !empty($this->recordedEvents);
    }

    /**
     * Clears all recorded events without returning them.
     */
    public function clearRecordedEvents(): void
    {
        $this->recordedEvents = [];
    }
}
