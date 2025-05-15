<?php

namespace App\Infrastructure\Session\Domain\Events;

use App\Infrastructure\Session\Domain\ValueObjects\SessionData;

/**
 * Dispatched when the session data is replaced or updated.
 *
 * Carries both the previous and updated session states.
 */
final class SessionDataChangedEvent
{
    /**
     * @param SessionData $previousData The session data before the change.
     * @param SessionData $newData      The session data after the change.
     */
    public function __construct(
        private readonly SessionData $previousData,
        private readonly SessionData $newData
    ) {}

    /**
     * Returns the previous session state.
     */
    public function getPreviousData(): SessionData
    {
        return $this->previousData;
    }

    /**
     * Returns the updated session state.
     */
    public function getNewData(): SessionData
    {
        return $this->newData;
    }
}
