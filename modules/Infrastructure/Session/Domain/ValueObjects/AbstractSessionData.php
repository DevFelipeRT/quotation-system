<?php

declare(strict_types=1);

namespace Session\Domain\ValueObjects;

use Session\Domain\Contracts\SessionDataInterface;

/**
 * Abstract base for session data value objects.
 *
 * Provides common accessors for contextual session metadata such as locale
 * and authentication state, and enforces immutability.
 *
 * All concrete session data types must extend this class and implement serialization.
 */
abstract class AbstractSessionData implements SessionDataInterface
{
    protected SessionContext $context;

    /**
     * Initializes a new session data instance with contextual metadata.
     *
     * @param SessionContext $context Contextual session metadata (locale, authentication status).
     */
    public function __construct(SessionContext $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the session context.
     *
     * @return SessionContext
     */
    public function getContext(): SessionContext
    {
        return $this->context;
    }

    /**
     * Returns the locale for the session.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->context->getLocale();
    }

    /**
     * Returns whether the session is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->context->isAuthenticated();
    }

    /**
     * Converts the session data object to an associative array.
     *
     * Must be implemented by concrete subclasses.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
