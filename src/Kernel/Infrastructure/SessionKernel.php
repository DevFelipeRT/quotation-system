<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure;

use App\Infrastructure\Session\Domain\Contracts\SessionHandlerInterface;
use App\Infrastructure\Session\Domain\Contracts\SessionHandlerResolverInterface;
use App\Infrastructure\Session\Domain\Contracts\SessionDataInterface;
use App\Infrastructure\Session\Domain\Events\SessionStartedEvent;
use App\Infrastructure\Session\Domain\Events\SessionDestroyedEvent;
use App\Infrastructure\Session\Domain\Events\SessionDataChangedEvent;
use App\Infrastructure\Session\Domain\ValueObjects\SessionData;
use App\Infrastructure\Session\Exceptions\SessionStartException;
use App\Infrastructure\Session\Exceptions\SessionDestroyException;
use App\Infrastructure\Session\Exceptions\UnsupportedSessionDriverException;
use App\Shared\Event\Contracts\EventDispatcherInterface;


/**
 * Coordinates session lifecycle and emits structured session events.
 *
 * Central entry point for all session-related behavior.
 */
final class SessionKernel
{
    private SessionHandlerInterface $handler;

    public function __construct(
        private readonly SessionHandlerResolverInterface $resolver,
        private readonly EventDispatcherInterface $dispatcher
    ) {
        $this->handler = $this->resolveHandler();
    }

    /**
     * Starts the session and emits SessionStartedEvent with session ID and data.
     *
     * @throws SessionStartException
     */
    public function start(): void
    {
        try {
            $this->handler->start();
        } catch (\Throwable $e) {
            throw new SessionStartException('Unable to start session.', [], 0, $e);
        }

        $data = $this->handler->getData();

        $this->dispatcher->dispatch(
            new SessionStartedEvent(
                session_id(),
                $this->assertConcrete($data)
            )
        );
    }

    /**
     * Returns the current session data object.
     */
    public function getData(): SessionDataInterface
    {
        return $this->handler->getData();
    }

    /**
     * Stores new session data and emits SessionDataChangedEvent.
     *
     * @param SessionDataInterface $data
     */
    public function setData(SessionDataInterface $data): void
    {
        $previous = $this->handler->getData();

        $this->handler->setData($data);

        $this->dispatcher->dispatch(
            new SessionDataChangedEvent(
                $this->assertConcrete($previous),
                $this->assertConcrete($data)
            )
        );
    }

    /**
     * Clears all session data but keeps the session open.
     */
    public function clear(): void
    {
        $this->handler->clearData();
    }

    /**
     * Destroys the session and emits SessionDestroyedEvent with last known data.
     *
     * @throws SessionDestroyException
     */
    public function destroy(): void
    {
        $data = $this->handler->getData();

        try {
            $this->handler->destroy();
        } catch (\Throwable $e) {
            throw new SessionDestroyException('Failed to destroy session.', [], 0, $e);
        }

        $this->dispatcher->dispatch(
            new SessionDestroyedEvent(
                $this->assertConcrete($data)
            )
        );
    }

    /**
     * Ensures the SessionDataInterface is a SessionData instance.
     *
     * @param SessionDataInterface $data
     * @return SessionData
     */
    private function assertConcrete(SessionDataInterface $data): SessionData
    {
        if (!$data instanceof SessionData) {
            throw new \LogicException('Session event dispatch requires concrete SessionData object.');
        }

        return $data;
    }

    /**
     * Resolves and validates the configured session handler implementation.
     *
     * @throws UnsupportedSessionDriverException
     */
    private function resolveHandler(): SessionHandlerInterface
    {
        return $this->resolver->resolve();
    }
}
