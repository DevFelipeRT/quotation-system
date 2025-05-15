<?php

namespace App\Infrastructure\Session\Domain\Events;

use App\Infrastructure\Session\Domain\ValueObjects\SessionData;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Dispatched when a session is destroyed.
 *
 * Carries the final state of the session and the exact UTC timestamp.
 */
final class SessionDestroyedEvent
{
    private readonly DateTimeImmutable $occurredAt;

    /**
     * @param SessionData $previousData The last known state before destruction.
     */
    public function __construct(
        private readonly SessionData $previousData
    ) {
        $this->occurredAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    /**
     * Returns the session data that was active before destruction.
     */
    public function getPreviousData(): SessionData
    {
        return $this->previousData;
    }

    /**
     * Returns the UTC timestamp when the session was destroyed.
     */
    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
