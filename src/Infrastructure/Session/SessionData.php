<?php

namespace App\Infrastructure\Session;

/**
 * Value Object representing all user session data.
 *
 * Immutable by design.
 */
final class SessionData
{
    private ?int $userId;
    private ?string $userName;
    private ?string $userRole;
    private string $locale;

    /**
     * @param int|null $userId User identifier (null if guest).
     * @param string|null $userName User's name (null if guest).
     * @param string|null $userRole User's role (null if guest).
     * @param string $locale Current user locale (default pt_BR).
     */
    public function __construct(
        ?int $userId = null,
        ?string $userName = null,
        ?string $userRole = null,
        string $locale = 'pt_BR'
    ) {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->userRole = $userRole;
        $this->locale = $locale;
    }

    /**
     * Returns the user ID if authenticated.
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Returns the user name if authenticated.
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * Returns the user role if authenticated.
     */
    public function getUserRole(): ?string
    {
        return $this->userRole;
    }

    /**
     * Returns the user locale preference.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Checks whether the user is authenticated.
     */
    public function isAuthenticated(): bool
    {
        return $this->userId !== null;
    }

    /**
     * Serializes the session data into an associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'user_role' => $this->userRole,
            'locale' => $this->locale,
        ];
    }

    /**
     * Creates a new SessionData instance from an array.
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['user_id'] ?? null,
            $data['user_name'] ?? null,
            $data['user_role'] ?? null,
            $data['locale'] ?? 'pt_BR'
        );
    }

    /**
     * Creates a new SessionData instance with updated user information.
     *
     * @param int $id
     * @param string $name
     * @param string $role
     * @return static
     */
    public function withUser(int $id, string $name, string $role): self
    {
        return new self(
            userId: $id,
            userName: $name,
            userRole: $role,
            locale: $this->locale
        );
    }

    /**
     * Creates a guest (unauthenticated) SessionData instance.
     *
     * @return static
     */
    public static function guest(): self
    {
        return new self();
    }
}
