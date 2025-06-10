<?php

declare(strict_types=1);

namespace Persistence\Domain\ValueObject;

use Persistence\Domain\Contract\QueryInterface;

/**
 * Secure, immutable Value Object representing a parametrized SQL query.
 *
 * Enforces safe query patterns and parameter types, and prevents
 * exposure of sensitive data during serialization or logging.
 *
 * Usage:
 *   $query = new Query('SELECT * FROM users WHERE email = :email', ['email' => 'foo@bar.com']);
 *
 * @package Persistence\Domain\ValueObject
 */
final class Query implements QueryInterface
{
    private string $sql;

    /** @var array<string, scalar|null> */
    private array $bindings;

    /**
     * Constructs a new secure Query object.
     *
     * @param string $sql The parametrized SQL statement (with :placeholders).
     * @param array<string, scalar|null> $bindings
     *
     * @throws \InvalidArgumentException if unsafe SQL or parameter types are detected.
     */
    public function __construct(string $sql, array $bindings = [])
    {
        $this->assertSafeSql($sql);
        $this->assertValidBindings($bindings);

        $this->sql = $sql;
        $this->bindings = $this->sanitizeBindings($bindings);
    }

    /**
     * Returns the SQL string.
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Returns the array of parameter bindings.
     *
     * @return array<string, scalar|null>
     */
    public function getBindings(): array
    {
        // Retorna cópia para garantir imutabilidade externa
        return $this->bindings;
    }

    /**
     * Custom debug info: masks parameter values to avoid accidental leaks.
     */
    public function __debugInfo(): array
    {
        return [
            'sql' => $this->sql,
            'bindings' => array_map(
                fn($value) => is_string($value) ? '***' : $value,
                $this->bindings
            ),
        ];
    }

    /**
     * Prevents object from being cast to string to avoid accidental logging.
     */
    public function __toString(): string
    {
        return 'Query(secure)';
    }

    /**
     * Checks that the SQL string is parametrized and does not contain dangerous patterns.
     */
    private function assertSafeSql(string $sql): void
    {
        if (preg_match('/(\?|%[a-zA-Z]|;.*;)/', $sql)) {
            throw new \InvalidArgumentException('Unsafe or non-parametrized SQL detected.');
        }

        // Critical commands block (customizable)
        if (preg_match('/\b(DROP|TRUNCATE|ALTER)\b/i', $sql)) {
            throw new \InvalidArgumentException('Dangerous SQL command detected.');
        }
    }

    /**
     * Checks that all binding values are scalar or null, and keys are valid.
     */
    private function assertValidBindings(array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            if (!is_string($key) || strpos($key, ':') !== 0) {
                throw new \InvalidArgumentException('Binding keys must be named placeholders (e.g. ":name").');
            }
            if (!is_scalar($value) && $value !== null) {
                throw new \InvalidArgumentException(
                    'Binding values must be scalar types or null (found ' . gettype($value) . ').'
                );
            }
        }
    }

    /**
     * Normalizes binding values (casts to appropriate scalar types).
     *
     * @param array<string, scalar|null> $bindings
     * @return array<string, scalar|null>
     */
    private function sanitizeBindings(array $bindings): array
    {
        $sanitized = [];
        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $sanitized[$key] = (int) $value;
            } elseif (is_float($value) || is_int($value) || is_null($value) || is_string($value)) {
                $sanitized[$key] = $value;
            } else {
                $sanitized[$key] = null;
            }
        }
        return $sanitized;
    }
}
