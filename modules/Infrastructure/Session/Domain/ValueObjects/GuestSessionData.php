<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Domain\ValueObjects;

/**
 * Represents a guest (unauthenticated) session.
 *
 * Provides access to contextual metadata (locale, authentication state)
 * and ensures serialization format is consistent for all session data objects.
 *
 * Immutable by construction.
 */
final class GuestSessionData extends AbstractSessionData
{
    /**
     * Initializes the guest session data with the given context.
     *
     * @param SessionContext $context The session context (locale, authentication state).
     */
    public function __construct(SessionContext $context)
    {
        parent::__construct($context);
    }

    /**
     * Converts the guest session data to an associative array.
     *
     * The format is consistent with AuthenticatedSessionData, but user fields are null.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id'       => null,
            'user_name'     => null,
            'user_role'     => null,
            'locale'        => $this->getLocale(),
            'authenticated' => false,
        ];
    }
}
