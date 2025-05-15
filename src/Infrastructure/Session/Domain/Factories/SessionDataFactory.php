<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Domain\Factories;

use App\Infrastructure\Session\Domain\Contracts\SessionDataInterface;
use App\Infrastructure\Session\Domain\ValueObjects\SessionContext;
use App\Infrastructure\Session\Domain\ValueObjects\UserIdentity;
use App\Infrastructure\Session\Domain\ValueObjects\AuthenticatedSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\GuestSessionData;
use App\Infrastructure\Session\Exceptions\InvalidSessionDataException;

/**
 * Reconstructs immutable session data objects from serialized representations.
 */
final class SessionDataFactory
{
    /**
     * Rebuilds a session data object from an associative array.
     *
     * @param array<string, mixed> $data Serialized session state.
     *
     * @return SessionDataInterface
     *
     * @throws InvalidSessionDataException
     */
    public static function fromArray(array $data): SessionDataInterface
    {
        $context = self::buildContext($data);

        if (self::hasIdentityFields($data)) {
            return self::buildAuthenticated($data, $context);
        }

        return new GuestSessionData($context);
    }

    /**
     * Constructs a SessionContext from array data.
     *
     * @param array<string, mixed> $data
     * @return SessionContext
     *
     * @throws InvalidSessionDataException
     */
    private static function buildContext(array $data): SessionContext
    {
        if (!isset($data['locale']) || !is_string($data['locale'])) {
            throw new InvalidSessionDataException("Missing or invalid 'locale' in session data.");
        }

        return new SessionContext(
            $data['locale'],
            (bool) ($data['authenticated'] ?? false)
        );
    }

    /**
     * Determines whether identity fields are present and well-formed.
     *
     * @param array<string, mixed> $data
     * @return bool
     */
    private static function hasIdentityFields(array $data): bool
    {
        return isset($data['user_id'], $data['user_name'], $data['user_role'])
            && is_int($data['user_id'])
            && is_string($data['user_name'])
            && is_string($data['user_role']);
    }

    /**
     * Constructs an authenticated session from array data.
     *
     * @param array<string, mixed> $data
     * @param SessionContext $context
     * @return AuthenticatedSessionData
     *
     * @throws InvalidSessionDataException
     */
    private static function buildAuthenticated(array $data, SessionContext $context): AuthenticatedSessionData
    {
        try {
            $identity = new UserIdentity(
                $data['user_id'],
                $data['user_name'],
                $data['user_role']
            );

            return new AuthenticatedSessionData($identity, $context);
        } catch (\Throwable $e) {
            throw new InvalidSessionDataException(
                "Invalid user identity data: {$e->getMessage()}",
                previous: $e
            );
        }
    }
}
