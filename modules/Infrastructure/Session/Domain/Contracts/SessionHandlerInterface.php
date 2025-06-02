<?php

declare(strict_types=1);

namespace Session\Domain\Contracts;

/**
 * Contract for session handlers that manage the lifecycle and contents of a session.
 *
 * Implementations must be capable of starting, retrieving, updating,
 * clearing, and destroying session data in a consistent manner.
 */
interface SessionHandlerInterface
{
    /**
     * Starts the session if it is not already active.
     *
     * @return void
     */
    public function start(): void;

    /**
     * Retrieves the current session data object.
     *
     * @return SessionDataInterface
     */
    public function getData(): SessionDataInterface;

    /**
     * Replaces the session data with the given object.
     *
     * @param SessionDataInterface $data
     * @return void
     */
    public function setData(SessionDataInterface $data): void;

    /**
     * Removes all stored values from the session but keeps it open.
     *
     * @return void
     */
    public function clearData(): void;

    /**
     * Destroys the session and clears all associated resources.
     *
     * @return void
     */
    public function destroy(): void;
}
