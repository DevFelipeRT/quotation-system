<?php

namespace App\Interfaces\Infrastructure;

use App\Infrastructure\Session\SessionData;

/**
 * Defines the contract for managing user session data.
 */
interface SessionInterface
{
    /**
     * Starts the session if not already active.
     */
    public function start(): void;

    /**
     * Retrieves the current session data.
     *
     * @return SessionData
     */
    public function getData(): SessionData;

    /**
     * Overwrites the session data with a new SessionData instance.
     *
     * @param SessionData $data
     */
    public function setData(SessionData $data): void;

    /**
     * Clears all user data stored in the session without destroying the session itself.
     */
    public function clearData(): void;

    /**
     * Completely destroys the session and its underlying storage.
     */
    public function destroySession(): void;
}
