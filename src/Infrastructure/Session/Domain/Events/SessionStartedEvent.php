<?php

namespace App\Infrastructure\Session\Domain\Events;

use App\Infrastructure\Session\Domain\ValueObjects\SessionData;

/**
 * Dispatched when a session is successfully started.
 *
 * Carries the initial session state and unique session identifier.
 */
final class SessionStartedEvent
{
    /**
     * @param string $sessionId  The PHP session ID at the moment of initialization.
     * @param SessionData $data  The full session state when the session began.
     */
    public function __construct(
        private readonly string $sessionId,
        private readonly SessionData $data
    ) {}

    /**
     * Returns the PHP session ID (e.g. from session_id()).
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * Returns the session data at the moment the session started.
     */
    public function getData(): SessionData
    {
        return $this->data;
    }
}
