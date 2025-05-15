<?php

namespace App\Infrastructure\Session\Infrastructure\Drivers;

use App\Infrastructure\Session\Domain\Contracts\SessionHandlerInterface;
use App\Infrastructure\Session\Infrastructure\ValueObjects\SessionData;
use App\Infrastructure\Session\Infrastructure\Exceptions\SessionStartException;
use App\Infrastructure\Session\Infrastructure\Exceptions\SessionDestroyException;

/**
 * Native PHP session implementation of SessionHandlerInterface.
 *
 * This class encapsulates native session lifecycle operations
 * and provides a structured interface for working with SessionData.
 */
final class NativeSessionHandler implements SessionHandlerInterface
{
    private bool $started = false;
    private const DATA_KEY = '__data__';

    public function __construct()
    {
        $this->start();
    }

    /**
     * Starts the session if it is not already active.
     *
     * @throws SessionStartException If session cannot be started.
     */
    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (headers_sent()) {
            throw new SessionStartException('Cannot start session: headers already sent.');
        }

        if (!session_start()) {
            throw new SessionStartException('Failed to start session.');
        }

        $this->started = true;
    }

    /**
     * Retrieves the current session data object.
     *
     * @return SessionData
     */
    public function getData(): SessionData
    {
        $data = $_SESSION[self::DATA_KEY] ?? [];
        return SessionData::fromArray($data);
    }

    /**
     * Replaces the session data with the given object.
     *
     * @param SessionData $data
     * @return void
     */
    public function setData(SessionData $data): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new SessionStartException('Cannot write to session: session is not active.');
        }

        $_SESSION[self::DATA_KEY] = $data->toArray();
    }

    /**
     * Removes all stored values from the session but keeps it open.
     *
     * @return void
     */
    public function clearData(): void
    {
        $_SESSION[self::DATA_KEY] = [];
    }

    /**
     * Destroys the session and clears all associated resources.
     *
     * @throws SessionDestroyException If destruction fails.
     *
     * @return void
     */
    public function destroy(): void
    {
        $this->clearData();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();

            if (!session_destroy()) {
                throw new SessionDestroyException('Session destruction failed.');
            }
        }

        $this->started = false;
    }
}
