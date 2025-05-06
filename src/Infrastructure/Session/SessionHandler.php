<?php

namespace App\Infrastructure\Session;

/**
 * Native PHP session implementation of SessionInterface.
 */
final class SessionHandler implements SessionHandlerInterface
{
    private bool $started = false;
    private const DATA_KEY = '__data__';

    public function __construct()
    {
        $this->start();
    }

    /**
     * {@inheritdoc}
     */
    public function start(): void
    {
        if (!$this->started && session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
            $this->started = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): SessionData
    {
        $data = $_SESSION[self::DATA_KEY] ?? [];
        return SessionData::fromArray($data);
    }

    /**
     * {@inheritdoc}
     */
    public function setData(SessionData $data): void
    {
        $_SESSION[self::DATA_KEY] = $data->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function clearData(): void
    {
        $_SESSION[self::DATA_KEY] = [];
    }

    /**
     * {@inheritdoc}
     */
    public function destroySession(): void
    {
        $this->clearData();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        $this->started = false;
    }
}
