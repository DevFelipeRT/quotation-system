<?php

declare(strict_types=1);

namespace Session\Domain\Events;

use Session\Domain\Contracts\SessionDataInterface;

/**
 * Domain event dispatched when the session data is updated or replaced.
 *
 * Carries both the previous and the new session data objects.
 */
final class SessionDataChangedEvent
{
    /**
     * @param SessionDataInterface $previousData The session data before the change.
     * @param SessionDataInterface $newData      The session data after the change.
     */
    public function __construct(
        private readonly SessionDataInterface $previousData,
        private readonly SessionDataInterface $newData
    ) {}

    /**
     * Returns the session data before the update.
     *
     * @return SessionDataInterface
     */
    public function getPreviousData(): SessionDataInterface
    {
        return $this->previousData;
    }

    /**
     * Returns the updated session data.
     *
     * @return SessionDataInterface
     */
    public function getNewData(): SessionDataInterface
    {
        return $this->newData;
    }
}
