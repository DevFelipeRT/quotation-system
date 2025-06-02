<?php

declare(strict_types=1);

namespace Session\Infrastructure\Drivers;

use Session\Domain\Contracts\SessionHandlerInterface;
use Session\Domain\Contracts\SessionDataInterface;
use Session\Domain\Factories\SessionDataFactory;
use Session\Exceptions\SessionStartException;
use Session\Exceptions\SessionDestroyException;
use Session\Exceptions\InvalidSessionDataException;

/**
 * Native PHP session implementation of SessionHandlerInterface.
 *
 * Manages the raw session lifecycle and strictly enforces valid session data.
 */
final class NativeSessionHandler implements SessionHandlerInterface
{
    private bool $started = false;

    private const DATA_KEY = '__data__';

    public function __construct() {}

    /**
     * Ensures the session is started and ready for I/O.
     *
     * @throws SessionStartException
     */
    private function ensureSessionIsReady(): void
    {
        if ($this->isSessionAlreadyStarted()) {
            return;
        }
        $this->ensureHeadersAreIntact();
        $this->attemptStartSession();
        $this->markAsStarted();
    }

    /**
     * {@inheritDoc}
     */
    public function start(): void
    {
        $this->ensureSessionIsReady();
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): SessionDataInterface
    {
        $this->ensureSessionIsReady();
        $sessionArray = $this->readRawSessionData();

        // SessionDataFactory rigorously validates locale
        return SessionDataFactory::fromArray($sessionArray);
    }

    /**
     * {@inheritDoc}
     */
    public function setData(SessionDataInterface $data): void
    {
        $this->ensureSessionIsReady();

        // Rigorously require locale before persisting session data
        $locale = $data->getLocale();
        if (!is_string($locale) || trim($locale) === '') {
            throw new InvalidSessionDataException("Session data must contain a valid, non-empty locale.");
        }

        $this->writeSessionData($data);
    }

    /**
     * {@inheritDoc}
     */
    public function clearData(): void
    {
        $this->ensureSessionIsReady();
        $_SESSION[self::DATA_KEY] = [];
    }

    /**
     * {@inheritDoc}
     */
    public function destroy(): void
    {
        $this->clearData();
        if ($this->isSessionActive()) {
            $this->unsetSessionStorage();
            $this->attemptDestroySession();
        }
        $this->markAsNotStarted();
    }

    // ========== Private Methods ==========

    private function isSessionAlreadyStarted(): bool
    {
        return $this->started || session_status() === PHP_SESSION_ACTIVE;
    }

    private function ensureHeadersAreIntact(): void
    {
        if (headers_sent()) {
            throw new SessionStartException('Cannot start session: headers already sent.');
        }
    }

    private function attemptStartSession(): void
    {
        if (!session_start()) {
            throw new SessionStartException('Failed to start session.');
        }
    }

    private function markAsStarted(): void
    {
        $this->started = true;
    }

    private function readRawSessionData(): array
    {
        return $_SESSION[self::DATA_KEY] ?? [];
    }

    private function writeSessionData(SessionDataInterface $data): void
    {
        $_SESSION[self::DATA_KEY] = $data->toArray();
    }

    private function isSessionActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    private function unsetSessionStorage(): void
    {
        session_unset();
    }

    private function attemptDestroySession(): void
    {
        if (!session_destroy()) {
            throw new SessionDestroyException('Session destruction failed.');
        }
    }

    private function markAsNotStarted(): void
    {
        $this->started = false;
    }
}
