<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Domain\ValueObjects;

/**
 * Represents a session with an authenticated user.
 *
 * Encapsulates user identity and contextual session metadata (locale, authentication state).
 * Immutable by construction.
 */
final class AuthenticatedSessionData extends AbstractSessionData
{
    private UserIdentity $identity;

    /**
     * Initializes an authenticated session data object.
     *
     * @param UserIdentity   $identity The authenticated user's identity.
     * @param SessionContext $context  The session context (locale, authentication state).
     */
    public function __construct(UserIdentity $identity, SessionContext $context)
    {
        parent::__construct($context);
        $this->identity = $identity;
    }

    /**
     * Returns the authenticated user's identity.
     *
     * @return UserIdentity
     */
    public function getIdentity(): UserIdentity
    {
        return $this->identity;
    }

    /**
     * Converts the authenticated session data into an associative array.
     *
     * The format is compatible with deserialization by SessionDataFactory.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id'       => $this->identity->getId(),
            'user_name'     => $this->identity->getName(),
            'user_role'     => $this->identity->getRole(),
            'locale'        => $this->getLocale(),
            'authenticated' => true,
        ];
    }
}
