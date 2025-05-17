<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure;

use App\Infrastructure\Session\Application\SessionFactory;
use App\Infrastructure\Session\Domain\Contracts\SessionHandlerInterface;
use App\Infrastructure\Session\Domain\Contracts\SessionDataInterface;
use App\Infrastructure\Session\Domain\Events\SessionStartedEvent;
use App\Infrastructure\Session\Domain\Events\SessionDataChangedEvent;
use App\Infrastructure\Session\Domain\Events\SessionDestroyedEvent;
use App\Infrastructure\Session\Domain\ValueObjects\AuthenticatedSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\GuestSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\SessionContext;
use App\Infrastructure\Session\Domain\ValueObjects\UserIdentity;
use App\Infrastructure\Session\Exceptions\SessionStartException;
use App\Infrastructure\Session\Exceptions\SessionDestroyException;
use App\Infrastructure\Session\Exceptions\InvalidSessionDataException;
use App\Infrastructure\Session\Infrastructure\Resolvers\DefaultSessionHandlerResolver;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use Config\Session\SessionConfig;

/**
 * SessionKernel
 *
 * Orchestrates the lifecycle of application sessions, centralizing
 * the initialization, mutation, and teardown of session data,
 * and emitting all session-related domain events.
 *
 * This class is the only entry point for session lifecycle control
 * and strictly separates session operations from their event propagation.
 */
final class SessionKernel
{
    /**
     * The internal session handler implementation.
     */
    private readonly SessionHandlerInterface $handler;

    /**
     * Instantiates the kernel, preparing the session handler and dispatcher.
     *
     * @param SessionConfig $config The session configuration provider.
     * @param EventDispatcherInterface $dispatcher The event dispatcher for session events.
     */
    public function __construct(
        private readonly SessionConfig $config,
        private readonly EventDispatcherInterface $dispatcher
    ) {
        $this->handler = $this->initializeHandler();
    }

    /**
     * Initializes the session at the PHP level.
     *
     * Emits SessionStartedEvent only if valid session data are present.
     *
     * @throws SessionStartException If the native session cannot be started.
     */
    public function start(): void
    {
        $this->attemptSessionStart();

        try {
            $data = $this->retrieveSessionData();
            $this->emitSessionStarted($data);
        } catch (InvalidSessionDataException) {
            // If there is no valid session data, no event is dispatched.
        }
    }

    /**
     * Retrieves the current session data object.
     *
     * @return SessionDataInterface
     * @throws InvalidSessionDataException If session data are missing or malformed.
     */
    public function getData(): SessionDataInterface
    {
        return $this->retrieveSessionData();
    }

    /**
     * Persists new session data and emits the appropriate session event.
     *
     * Emits SessionDataChangedEvent if there was existing data,
     * otherwise emits SessionStartedEvent for first initialization.
     *
     * @param SessionDataInterface $data
     */
    public function setData(SessionDataInterface $data): void
    {
        $previous = null;
        try {
            $previous = $this->retrieveSessionData();
        } catch (InvalidSessionDataException) {
            // No previous valid session data existed.
        }

        $this->handler->setData($data);

        if ($previous !== null) {
            $this->emitSessionDataChanged($previous, $data);
        } else {
            $this->emitSessionStarted($data);
        }
    }

    /**
     * Clears all session data.
     *
     * No events are emitted for data clearance.
     */
    public function clear(): void
    {
        $this->handler->clearData();
    }

    /**
     * Destroys the session, removing all data and emitting SessionDestroyedEvent.
     *
     * @throws SessionDestroyException If destruction fails.
     */
    public function destroy(): void
    {
        try {
            $data = $this->retrieveSessionData();
        } catch (InvalidSessionDataException) {
            $data = null;
        }

        $this->attemptSessionDestruction();

        if ($data !== null) {
            $this->emitSessionDestroyed($data);
        }
    }

    /**
     * Initializes a new guest session with the default locale.
     *
     * The session is set to unauthenticated and emits all required events.
     */
    public function startGuestSession(): void
    {
        $locale = $this->config->defaultLocale();
        $guest = new GuestSessionData(new SessionContext($locale, false));
        $this->setData($guest);
    }

    /**
     * Initializes a new authenticated session for the provided user identity,
     * using the default locale and emitting all required events.
     *
     * @param UserIdentity $identity
     */
    public function startAuthenticatedSession(UserIdentity $identity): void
    {
        $locale = $this->config->defaultLocale();
        $authenticated = new AuthenticatedSessionData(
            $identity,
            new SessionContext($locale, true)
        );
        $this->setData($authenticated);
    }

    /**
     * Retrieves the locale from the current session data,
     * or returns the default locale if unavailable.
     *
     * @return string The preferred locale for this session context.
     */
    public function getLocale(): string
    {
        try {
            $data = $this->getData();
            return $data->getLocale();
        } catch (InvalidSessionDataException) {
            return $this->config->defaultLocale();
        }
    }

    // ======================= PRIVATE SRP METHODS =======================

    /**
     * Prepares the correct session handler based on configuration.
     *
     * @return SessionHandlerInterface
     */
    private function initializeHandler(): SessionHandlerInterface
    {
        $resolver = new DefaultSessionHandlerResolver($this->config);
        $factory = new SessionFactory($resolver);
        return $factory->create();
    }

    /**
     * Attempts to start the native PHP session, throwing on failure.
     *
     * @throws SessionStartException
     */
    private function attemptSessionStart(): void
    {
        try {
            $this->handler->start();
        } catch (\Throwable $e) {
            throw new SessionStartException('Unable to start session.', [], 0, $e);
        }
    }

    /**
     * Attempts to destroy the session, throwing on failure.
     *
     * @throws SessionDestroyException
     */
    private function attemptSessionDestruction(): void
    {
        try {
            $this->handler->destroy();
        } catch (\Throwable $e) {
            throw new SessionDestroyException('Failed to destroy session.', [], 0, $e);
        }
    }

    /**
     * Retrieves the session data via handler.
     *
     * @return SessionDataInterface
     * @throws InvalidSessionDataException If no valid session data are available.
     */
    private function retrieveSessionData(): SessionDataInterface
    {
        return $this->handler->getData();
    }

    /**
     * Emits a SessionStartedEvent for the provided session data.
     *
     * @param SessionDataInterface $data
     */
    private function emitSessionStarted(SessionDataInterface $data): void
    {
        $this->dispatcher->dispatch(new SessionStartedEvent(session_id(), $data));
    }

    /**
     * Emits a SessionDataChangedEvent for the previous and current data.
     *
     * @param SessionDataInterface $previous
     * @param SessionDataInterface $current
     */
    private function emitSessionDataChanged(SessionDataInterface $previous, SessionDataInterface $current): void
    {
        $this->dispatcher->dispatch(new SessionDataChangedEvent($previous, $current));
    }

    /**
     * Emits a SessionDestroyedEvent for the provided session data.
     *
     * @param SessionDataInterface $data
     */
    private function emitSessionDestroyed(SessionDataInterface $data): void
    {
        $this->dispatcher->dispatch(new SessionDestroyedEvent($data));
    }
}
