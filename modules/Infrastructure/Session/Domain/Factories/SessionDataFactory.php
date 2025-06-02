<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Domain\Factories;

use App\Infrastructure\Session\Domain\Contracts\SessionDataInterface;
use App\Infrastructure\Session\Domain\ValueObjects\SessionContext;
use App\Infrastructure\Session\Domain\ValueObjects\AuthenticatedSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\GuestSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\AbstractControllerSessionData;
use App\Infrastructure\Session\Exceptions\InvalidSessionDataException;

/**
 * SessionDataFactory
 *
 * Responsible for reconstructing session data value objects from associative arrays.
 * Supports both standard and controller-specific session data (such as decorators).
 *
 * Integrity is strictly enforced: invalid or incomplete arrays will result in an exception.
 */
final class SessionDataFactory
{
    /**
     * Reconstructs a session data object from its serialized representation.
     *
     * The factory will detect controller-specific session data via the 'controller' key,
     * and will call the respective VO's static fromArray() method. Decorators must serialize
     * their wrapped object under the 'base_session' key.
     *
     * @param array<string, mixed> $data
     * @return SessionDataInterface
     *
     * @throws InvalidSessionDataException
     */
    public static function fromArray(array $data): SessionDataInterface
    {
        if (self::isControllerSession($data)) {
            return self::instantiateControllerSession($data);
        }

        return self::instantiateStandardSession($data);
    }

    /**
     * Determines if the array encodes a controller-specific session data object.
     *
     * @param array<string, mixed> $data
     * @return bool
     */
    private static function isControllerSession(array $data): bool
    {
        return isset($data['controller'])
            && is_string($data['controller'])
            && class_exists($data['controller'])
            && is_subclass_of($data['controller'], AbstractControllerSessionData::class);
    }

    /**
     * Instantiates a controller-specific session data object using its fromArray contract.
     *
     * @param array<string, mixed> $data
     * @return SessionDataInterface
     *
     * @throws InvalidSessionDataException
     */
    private static function instantiateControllerSession(array $data): SessionDataInterface
    {
        $className = $data['controller'];

        if (!method_exists($className, 'fromArray')) {
            throw new InvalidSessionDataException(
                "Session data class '{$className}' must implement a static fromArray(array): self method."
            );
        }

        try {
            /** @var SessionDataInterface $object */
            $object = $className::fromArray($data);
        } catch (\Throwable $e) {
            throw new InvalidSessionDataException(
                "Controller session data reconstruction failed: {$e->getMessage()}",
                previous: $e
            );
        }

        if (!$object instanceof SessionDataInterface) {
            throw new InvalidSessionDataException(
                "Resulting object from '{$className}::fromArray' does not implement SessionDataInterface."
            );
        }

        return $object;
    }

    /**
     * Instantiates a standard session data object (guest or authenticated).
     *
     * @param array<string, mixed> $data
     * @return SessionDataInterface
     *
     * @throws InvalidSessionDataException
     */
    private static function instantiateStandardSession(array $data): SessionDataInterface
    {
        $isAuthenticated = self::hasIdentityFields($data);
        $context = self::buildContext($data, $isAuthenticated);

        if ($isAuthenticated) {
            return self::instantiateAuthenticatedSession($data, $context);
        }

        return new GuestSessionData($context);
    }

    /**
     * Builds a SessionContext from data.
     *
     * @param array<string, mixed> $data
     * @param bool $authenticated
     * @return SessionContext
     *
     * @throws InvalidSessionDataException
     */
    private static function buildContext(array $data, bool $authenticated): SessionContext
    {
        if (!isset($data['locale']) || !is_string($data['locale'])) {
            throw new InvalidSessionDataException("Missing or invalid 'locale' in session data.");
        }

        return new SessionContext(
            $data['locale'],
            $authenticated
        );
    }

    /**
     * Determines whether identity fields are present and valid.
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
     * Instantiates an AuthenticatedSessionData object from array.
     *
     * @param array<string, mixed> $data
     * @param SessionContext $context
     * @return AuthenticatedSessionData
     *
     * @throws InvalidSessionDataException
     */
    private static function instantiateAuthenticatedSession(array $data, SessionContext $context): AuthenticatedSessionData
    {
        try {
            $identity = UserIdentityFactory::fromArray($data);
            return new AuthenticatedSessionData($identity, $context);
        } catch (\Throwable $e) {
            throw new InvalidSessionDataException(
                "Invalid user identity data: {$e->getMessage()}",
                previous: $e
            );
        }
    }
}
