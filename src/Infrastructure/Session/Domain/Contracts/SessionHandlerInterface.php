<?php

namespace App\Infrastructure\Session\Domain\Contracts;

/**
 * Defines the contract for a session handler that manages the lifecycle and contents of a session.
 */
interface SessionHandlerInterface
{
    /**
     * Starts the session if it is not already active.
     */
    public function start(): void;

    /**
     * Retrieves the current session data object.
     */
    public function getData(): SessionDataInterface;

    /**
     * Replaces the session data with the given object.
     *
     * @param SessionData $data
     */
    public function setData(SessionDataInterface $data): void;

    /**
     * Removes all stored values from the session but keeps it open.
     */
    public function clearData(): void;

    /**
     * Destroys the session and clears all associated resources.
     */
    public function destroy(): void;
}
