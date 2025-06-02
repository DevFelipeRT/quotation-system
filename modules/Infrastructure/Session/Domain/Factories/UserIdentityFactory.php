<?php

declare(strict_types=1);

namespace Session\Domain\Factories;

use Session\Domain\ValueObjects\UserIdentity;
use Session\Exceptions\InvalidSessionIdentityException;

/**
 * UserIdentityFactory
 *
 * Responsável por construir instâncias validadas de UserIdentity a partir de arrays associativos,
 * tipicamente provenientes de serialização/deserialização de sessão.
 *
 * Garante integridade e validação de todos os campos de identidade.
 */
final class UserIdentityFactory
{
    /**
     * Reconstrói uma instância UserIdentity validada a partir de um array.
     *
     * @param array<string, mixed> $data
     * @return UserIdentity
     *
     * @throws InvalidSessionIdentityException Se faltar ou estiver inválido algum campo.
     */
    public static function fromArray(array $data): UserIdentity
    {
        self::assertRequiredFieldsExist($data);

        $id   = self::validateId($data['user_id']);
        $name = self::validateName($data['user_name']);
        $role = self::validateRole($data['user_role']);

        return new UserIdentity($id, $name, $role);
    }

    /**
     * Verifica se todos os campos obrigatórios existem.
     *
     * @param array<string, mixed> $data
     * @throws InvalidSessionIdentityException
     */
    private static function assertRequiredFieldsExist(array $data): void
    {
        foreach (['user_id', 'user_name', 'user_role'] as $field) {
            if (!array_key_exists($field, $data)) {
                throw new InvalidSessionIdentityException("Missing required identity field: {$field}");
            }
        }
    }

    /**
     * Valida o ID do usuário.
     *
     * @param mixed $id
     * @return int
     * @throws InvalidSessionIdentityException
     */
    private static function validateId(mixed $id): int
    {
        if (!is_int($id) || $id <= 0) {
            throw new InvalidSessionIdentityException('User ID must be a positive integer.');
        }
        return $id;
    }

    /**
     * Valida o nome do usuário.
     *
     * @param mixed $name
     * @return string
     * @throws InvalidSessionIdentityException
     */
    private static function validateName(mixed $name): string
    {
        if (!is_string($name) || trim($name) === '') {
            throw new InvalidSessionIdentityException('User name must be a non-empty string.');
        }
        return $name;
    }

    /**
     * Valida o papel do usuário.
     *
     * @param mixed $role
     * @return string
     * @throws InvalidSessionIdentityException
     */
    private static function validateRole(mixed $role): string
    {
        if (!is_string($role) || trim($role) === '') {
            throw new InvalidSessionIdentityException('User role must be a non-empty string.');
        }
        return $role;
    }
}
