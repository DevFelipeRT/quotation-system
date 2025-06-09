<?php

declare(strict_types=1);

namespace Config\Database;


use Config\Env\EnvLoader;

/**
 * Provides immutable and validated access to database connection parameters
 * retrieved from environment variables.
 */
final class DatabaseConfig
{
    private EnvLoader $env;
    private static $driversList = [
        'mysql',
        'pgsql',
        'sqlite',
    ];

    public function __construct(EnvLoader $env)
    {
        $this->env = $env;
    }
    
    public static function getDriversList(): array
    {
        return self::$driversList;
    }

    public function getDriver(): string
    {
        return $this->env->getRequired('DB_DRIVER');

    }

    public function getHost(): string
    {
        return $this->env->getRequired('DB_HOST');
    }

    public function getUsername(): string
    {
        return $this->env->getRequired('DB_USER');
    }

    public function getPassword(): string
    {
        return $this->env->getRequired('DB_PASS');
    }

    public function getDatabase(): string
    {
        return $this->env->getRequired('DB_NAME');
    }

    public function getPort(): int
    {
        return (int) $this->env->getRequired('DB_PORT');
    }

    public function getFile(): string
    {
        return $this->env->getRequired('DB_FILE');
    }

    /**
     * Returns additional PDO driver options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }
}
