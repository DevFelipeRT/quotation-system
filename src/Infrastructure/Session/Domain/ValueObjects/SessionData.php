<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Domain\ValueObjects;

use App\Infrastructure\Session\Domain\Contracts\SessionDataInterface;

/**
 * Abstract base class for all session data representations.
 *
 * Encapsulates shared behavior for both authenticated and guest sessions,
 * including access to session context and locale.
 *
 * Immutable by design.
 */
abstract class SessionData implements SessionDataInterface
{
    protected SessionContext $context;

    /**
     * Initializes session context.
     *
     * @param SessionContext $context Contextual metadata (locale, auth status).
     */
    public function __construct(SessionContext $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the session context object.
     */
    public function getContext(): SessionContext
    {
        return $this->context;
    }

    /**
     * Returns the locale defined for this session.
     */
    public function getLocale(): string
    {
        return $this->context->getLocale();
    }

    /**
     * Returns whether the session is authenticated.
     */
    public function isAuthenticated(): bool
    {
        return $this->context->isAuthenticated();
    }

    /**
     * Converts the session data to an associative array.
     *
     * Must be implemented by concrete subclasses.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
