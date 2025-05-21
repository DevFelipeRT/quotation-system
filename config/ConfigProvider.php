<?php

namespace Config;

use Config\App\AppConfig;
use Config\Database\DatabaseConfig;
use Config\Database\DatabaseSchemaConfig;
use Config\Env\EnvLoader;
use Config\Kernel\KernelConfig;
use Config\Paths\PathsConfig;
use Config\Paths\LogPathsConfig;
use Config\Session\SessionConfig;
use InvalidArgumentException;

/**
 * Class ConfigProvider
 *
 * Aggregates and provides centralized access to all configuration modules
 * used throughout the application. Instantiated once during bootstrap.
 *
 * @package Config
 */
final class ConfigProvider
{
    private PathsConfig           $pathsConfig;
    private EnvLoader             $envLoader;
    private AppConfig             $appConfig;
    private DatabaseConfig        $databaseConfig;
    private DatabaseSchemaConfig  $schemaConfig;
    private LogPathsConfig        $logPathsConfig;
    private SessionConfig         $sessionConfig;
    private KernelConfig          $kernelConfig;

    /**
     * ConfigProvider constructor.
     *
     * Initializes all configuration modules in correct dependency order.
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->pathsConfig     = new PathsConfig();
        $this->envLoader       = new EnvLoader($this->pathsConfig->getEnvFilePath());
        $this->appConfig       = new AppConfig($this->envLoader);
        $this->databaseConfig  = new DatabaseConfig($this->envLoader);
        $this->schemaConfig    = new DatabaseSchemaConfig();
        $this->logPathsConfig  = new LogPathsConfig($this->pathsConfig);
        $this->sessionConfig   = new SessionConfig();
        $this->kernelConfig    = new KernelConfig();
    }

    /** @return PathsConfig */
    public function getPathsConfig(): PathsConfig
    {
        return $this->pathsConfig;
    }

    /** @return EnvLoader */
    public function getEnvLoader(): EnvLoader
    {
        return $this->envLoader;
    }

    /** @return AppConfig */
    public function getAppConfig(): AppConfig
    {
        return $this->appConfig;
    }

    /** @return DatabaseConfig */
    public function getDatabaseConfig(): DatabaseConfig
    {
        return $this->databaseConfig;
    }

    /** @return DatabaseSchemaConfig */
    public function getSchemaConfig(): DatabaseSchemaConfig
    {
        return $this->schemaConfig;
    }

    /** @return LogPathsConfig */
    public function getLogPathsConfig(): LogPathsConfig
    {
        return $this->logPathsConfig;
    }

    /** @return SessionConfig */
    public function getSessionConfig(): SessionConfig
    {
        return $this->sessionConfig;
    }

    /** @return KernelConfig */
    public function getKernelConfig(): KernelConfig
    {
        return $this->kernelConfig;
    }
}