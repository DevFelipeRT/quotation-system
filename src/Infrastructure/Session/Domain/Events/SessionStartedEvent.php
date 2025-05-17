<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Domain\Events;

use App\Infrastructure\Session\Domain\Contracts\SessionDataInterface;

/**
 * Domain event dispatched when a session is successfully started.
 *
 * Carries the unique session identifier and its initial state.
 */
final class SessionStartedEvent
{
    /**
     * @param string $sessionId               The PHP session ID at the moment of initialization.
     * @param SessionDataInterface $data     The session data at the time the session began.
     */
    public function __construct(
        private readonly string $sessionId,
        private readonly SessionDataInterface $data
    ) {}

    /**
     * Returns the PHP session ID (e.g., from session_id()).
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * Returns the session data at the moment the session started.
     *
     * @return SessionDataInterface
     */
    public function getData(): SessionDataInterface
    {
        return $this->data;
    }
}
