<?php

namespace Config\Container;

use Config\App\AppConfig;
use Config\Database\DatabaseConfig;
use Config\Database\DatabaseSchemaConfig;
use Config\Env\EnvLoader;
use Config\Logs\LogsConfig;
use Config\Paths\PathsConfig;
use InvalidArgumentException;

/**
 * ConfigContainer
 *
 * Aggregates and provides centralized access to all configuration modules
 * used throughout the application. Each configuration segment is responsible
 * for a distinct domain (environment, paths, database, logging, etc.).
 *
 * This container is designed to be instantiated once at application bootstrap.
 */
class ConfigContainer
{
    /**
     * Filesystem and structure-related paths.
     */
    private PathsConfig $paths;

    /**
     * Secure, stateless environment loader.
     */
    private EnvLoader $env;

    /**
     * Application-level configuration (name, environment, debug mode).
     */
    private AppConfig $app;

    /**
     * Database connection credentials and port.
     */
    private DatabaseConfig $database;

    /**
     * Logical mapping of database structure (tables and fields).
     */
    private DatabaseSchemaConfig $schema;

    /**
     * Paths to log files used in various subsystems.
     */
    private LogsConfig $logs;

    /**
     * Initializes all configuration modules in correct dependency order.
     *
     * @throws InvalidArgumentException If any component fails to initialize.
     */
    public function __construct()
    {
        // Dependency order must be strictly observed.
        $this->paths    = new PathsConfig();
        $this->env      = new EnvLoader($this->paths->envFile());
        $this->app      = new AppConfig($this->env);
        $this->database = new DatabaseConfig($this->env);
        $this->schema   = new DatabaseSchemaConfig();
        $this->logs     = new LogsConfig();
    }

    /**
     * Returns application-level configuration.
     */
    public function app(): AppConfig
    {
        return $this->app;
    }

    /**
     * Returns runtime paths configuration.
     */
    public function paths(): PathsConfig
    {
        return $this->paths;
    }

    /**
     * Returns the environment variable loader.
     */
    public function env(): EnvLoader
    {
        return $this->env;
    }

    /**
     * Returns the database connection configuration.
     */
    public function database(): DatabaseConfig
    {
        return $this->database;
    }

    /**
     * Returns logical schema mapping of the database.
     */
    public function schema(): DatabaseSchemaConfig
    {
        return $this->schema;
    }

    /**
     * Returns all configured log file paths.
     */
    public function logs(): LogsConfig
    {
        return $this->logs;
    }
}
