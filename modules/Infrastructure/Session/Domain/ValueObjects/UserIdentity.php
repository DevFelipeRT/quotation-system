<?php

declare(strict_types=1);

namespace Session\Domain\ValueObjects;

use Session\Exceptions\InvalidSessionIdentityException;

/**
 * Represents the identity of an authenticated user within a session context.
 *
 * Immutable by design. Validated on construction.
 */
final class UserIdentity
{
    private int $id;
    private string $name;
    private string $role;

    /**
     * Constructs a new UserIdentity instance.
     *
     * @param int $id Positive numeric identifier.
     * @param string $name Non-empty display name.
     * @param string $role Non-empty permission group.
     *
     * @throws InvalidSessionIdentityException
     */
    public function __construct(int $id, string $name, string $role)
    {
        $this->id = $this->validateId($id);
        $this->name = $this->validateName($name);
        $this->role = $this->validateRole($role);
    }

    /**
     * Returns the user's unique ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the user's display name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the user's role or permission group.
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Validates and returns a positive user ID.
     *
     * @throws InvalidSessionIdentityException
     */
    private function validateId(int $id): int
    {
        if ($id <= 0) {
            throw new InvalidSessionIdentityException('User ID must be a positive integer.');
        }

        return $id;
    }

    /**
     * Validates and returns a non-empty user name.
     *
     * @throws InvalidSessionIdentityException
     */
    private function validateName(string $name): string
    {
        if (trim($name) === '') {
            throw new InvalidSessionIdentityException('User name cannot be empty.');
        }

        return $name;
    }

    /**
     * Validates and returns a non-empty user role.
     *
     * @throws InvalidSessionIdentityException
     */
    private function validateRole(string $role): string
    {
        if (trim($role) === '') {
            throw new InvalidSessionIdentityException('User role cannot be empty.');
        }

        return $role;
    }
}
