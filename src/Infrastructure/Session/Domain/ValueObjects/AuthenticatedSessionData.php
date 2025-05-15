<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Domain\ValueObjects;

use App\Infrastructure\Session\Exceptions\InvalidSessionDataException;

/**
 * Represents a session with an authenticated user.
 *
 * Provides access to user identity and session context.
 * Immutable and validated by construction.
 */
final class AuthenticatedSessionData extends SessionData
{
    private UserIdentity $identity;

    /**
     * Constructs an authenticated session representation.
     *
     * @param UserIdentity   $identity Valid user identity.
     * @param SessionContext $context  Contextual session metadata.
     */
    public function __construct(UserIdentity $identity, SessionContext $context)
    {
        parent::__construct($context);
        $this->identity = $identity;
    }

    /**
     * Returns the authenticated user's identity.
     */
    public function getIdentity(): UserIdentity
    {
        return $this->identity;
    }

    /**
     * Converts the session data into an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id'       => $this->identity->getId(),
            'user_name'     => $this->identity->getName(),
            'user_role'     => $this->identity->getRole(),
            'locale'        => $this->context->getLocale(),
            'authenticated' => true,
        ];
    }
}
