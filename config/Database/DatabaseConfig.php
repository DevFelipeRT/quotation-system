<?php

namespace Config\Database;

use Config\Env\EnvLoader;
use InvalidArgumentException;

/**
 * DatabaseConfig
 *
 * Provides validated access to database connection parameters via
 * environment variables. Avoids storing sensitive data in memory,
 * exposing values only through controlled accessors.
 */
final class DatabaseConfig
{
    private EnvLoader $env;

    /**
     * Constructor
     *
     * @param EnvLoader $env The environment loader instance.
     */
    public function __construct(EnvLoader $env)
    {
        $this->env = $env;
    }

    /**
     * Get the database driver (e.g., mysql, pgsql, sqlite).
     *
     * @return string
     * @throws InvalidArgumentException if the driver is unsupported.
     */
    public function driver(): string
    {
        $driver = strtolower(trim((string) getenv('DB_DRIVER')));

        return match ($driver) {
            'mysql', 'pgsql', 'sqlite' => $driver,
            '', null                  => 'mysql', // default fallback
            default                   => throw new InvalidArgumentException("Unsupported DB_DRIVER: {$driver}")
        };
    }

    /**
     * Get the database host.
     *
     * @return string
     */
    public function host(): string
    {
        return $this->env->getRequired('DB_HOST');
    }

    /**
     * Get the database username.
     *
     * @return string
     */
    public function username(): string
    {
        return $this->env->getRequired('DB_USER');
    }

    /**
     * Get the database password.
     *
     * @return string
     */
    public function password(): string
    {
        return $this->env->getRequired('DB_PASS');
    }

    /**
     * Get the database name.
     *
     * @return string
     */
    public function database(): string
    {
        return $this->env->getRequired('DB_NAME');
    }

    /**
     * Get the database port.
     *
     * @return int
     */
    public function port(): int
    {
        return (int) $this->env->getRequired('DB_PORT');
    }
}
