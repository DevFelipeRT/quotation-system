<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Domain\ValueObjects;

/**
 * Represents an anonymous (unauthenticated) session.
 *
 * Encapsulates session context such as locale, without user identity.
 * Immutable and validated by construction.
 */
final class GuestSessionData extends SessionData
{
    /**
     * Constructs a guest session representation.
     *
     * @param SessionContext $context Valid session context.
     */
    public function __construct(SessionContext $context)
    {
        parent::__construct($context);
    }

    /**
     * Converts the session data into an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id'       => null,
            'user_name'     => null,
            'user_role'     => null,
            'locale'        => $this->context->getLocale(),
            'authenticated' => false,
        ];
    }
}
