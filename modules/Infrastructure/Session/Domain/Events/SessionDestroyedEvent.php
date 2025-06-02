<?php

declare(strict_types=1);

namespace Session\Domain\Events;

use Session\Domain\Contracts\SessionDataInterface;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Domain event dispatched when a session is destroyed.
 *
 * Carries the final state of the session and the UTC timestamp of destruction.
 */
final class SessionDestroyedEvent
{
    private readonly DateTimeImmutable $occurredAt;

    /**
     * @param SessionDataInterface $previousData The last known state before destruction.
     */
    public function __construct(
        private readonly SessionDataInterface $previousData
    ) {
        $this->occurredAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    /**
     * Returns the session data that was active before destruction.
     *
     * @return SessionDataInterface
     */
    public function getPreviousData(): SessionDataInterface
    {
        return $this->previousData;
    }

    /**
     * Returns the UTC timestamp when the session was destroyed.
     *
     * @return DateTimeImmutable
     */
    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
