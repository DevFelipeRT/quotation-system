<?php

namespace Config\Database;

use Config\Env\EnvLoader;

/**
 * Class DatabaseConfig
 *
 * Provides immutable and validated access to database connection parameters
 * retrieved from environment variables.
 *
 * @package Config\Database
 */
final class DatabaseConfig
{
    private EnvLoader $env;

    /**
     * DatabaseConfig constructor.
     *
     * @param EnvLoader $env Loader for environment variables.
     */
    public function __construct(EnvLoader $env)
    {
        $this->env = $env;
    }

    /**
     * Retrieves and validates the database driver.
     *
     * @return string
     */
    public function getDriver(): string
    {
        return SupportedDrivers::resolve(getenv('DB_DRIVER'));
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->env->getRequired('DB_HOST');
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->env->getRequired('DB_USER');
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->env->getRequired('DB_PASS');
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->env->getRequired('DB_NAME');
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return (int) $this->env->getRequired('DB_PORT');
    }
}
