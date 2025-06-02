<?php

declare(strict_types=1);

namespace Session\Domain\Contracts;

use Session\Domain\ValueObjects\SessionContext;

/**
 * Defines the contract for all session data representations.
 *
 * Implementations may represent either authenticated or guest sessions,
 * and must provide consistent access to the session context.
 */
interface SessionDataInterface
{
    /**
     * Returns the contextual metadata of the session.
     *
     * @return SessionContext
     */
    public function getContext(): SessionContext;

    /**
     * Returns whether the session is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Returns the locale associated with the session.
     *
     * @return string
     */
    public function getLocale(): string;

    /**
     * Serializes the session data into an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
