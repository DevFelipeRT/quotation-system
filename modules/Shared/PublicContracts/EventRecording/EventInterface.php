<?php

namespace PublicContracts\EventRecording;

/**
 * Interface EventInterface
 *
 * Defines the contract for all events in the application.
 * Domain Events represent something significant that happened in the domain.
 * They should be immutable and carry all necessary data about the occurrence.
 */
interface EventInterface
{
    /**
     * Returns the unique identifier for this event.
     * This could be a UUID or a sequential ID.
     *
     * @return string
     */
    public function getEventId(): string;

    /**
     * Returns the name or type of the event.
     * This helps in identifying the event for logging, routing, or processing.
     *
     * @return string
     */
    public function getEventName(): string;

    /**
     * Returns the timestamp when the event occurred.
     * It's crucial for event ordering and auditing.
     *
     * @return \DateTimeImmutable
     */
    public function getOccurredOn(): \DateTimeImmutable;

    /**
     * Returns the payload (data) of the event.
     * This should contain all relevant information about what happened,
     * typically as an associative array or an object.
     *
     * @return array|object
     */
    public function getPayload();

    /**
     * Converts the event to an array representation.
     * Useful for serialization, logging, or passing across boundaries.
     *
     * @return array
     */
    public function toArray(): array;
}