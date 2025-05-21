<?php

declare(strict_types=1);

namespace App\Kernel\Container;

use App\Shared\Container\AppContainerInterface;
use App\Shared\Container\AppContainer;
use Config\ConfigProvider;
use Config\App\AppConfig;
use Config\Database\DatabaseConfig;
use Config\Database\DatabaseSchemaConfig;
use Config\Paths\PathsConfig;
use Config\Paths\LogPathsConfig;
use Config\Session\SessionConfig;
use Config\Env\EnvLoader;

/**
 * ContainerKernel
 *
 * Centralizes the construction and provisioning of the application-wide dependency injection container.
 * Responsible for registering all essential configuration objects and global service bindings,
 * enforcing interface-driven access to dependencies throughout the system.
 *
 * @package App\Kernel\Container
 */
final class ContainerKernel
{
    private readonly AppContainerInterface $container;

    /**
     * Initializes the dependency injection container with all configuration and service bindings.
     *
     * @param ConfigProvider $configProvider
     * @param array<string, object> $serviceBindings [interface => instance]
     */
    public function __construct(ConfigProvider $configProvider, array $serviceBindings = [])
    {
        $this->validateConfigProvider($configProvider);
        $this->validateServiceBindings($serviceBindings);

        $this->container = $this->buildContainer($configProvider, $serviceBindings);
    }

    /**
     * Returns the fully initialized DI container.
     *
     * @return AppContainerInterface
     */
    public function getContainer(): AppContainerInterface
    {
        return $this->container;
    }

    /**
     * Builds and registers all system configurations and service bindings.
     *
     * @param ConfigProvider $configProvider
     * @param array<string, object> $serviceBindings
     * @return AppContainerInterface
     */
    private function buildContainer(
        ConfigProvider $configProvider,
        array $serviceBindings
    ): AppContainerInterface {
        $container = new AppContainer();

        $this->registerAllConfigurations($container, $configProvider);
        $this->registerAllServiceBindings($container, $serviceBindings);

        return $container;
    }

    /**
     * Registers all configuration objects in the container.
     *
     * @param AppContainerInterface $container
     * @param ConfigProvider $configProvider
     * @return void
     */
    private function registerAllConfigurations(
        AppContainerInterface $container,
        ConfigProvider $configProvider
    ): void {
        $this->registerConfigProvider($container, $configProvider);
        $this->registerPathsConfig($container, $configProvider->getPathsConfig());
        $this->registerEnvLoader($container, $configProvider->getEnvLoader());
        $this->registerAppConfig($container, $configProvider->getAppConfig());
        $this->registerDatabaseConfig($container, $configProvider->getDatabaseConfig());
        $this->registerDatabaseSchemaConfig($container, $configProvider->getSchemaConfig());
        $this->registerLogPathsConfig($container, $configProvider->getLogPathsConfig());
        $this->registerSessionConfig($container, $configProvider->getSessionConfig());
    }

    /**
     * Registers the ConfigProvider instance.
     */
    private function registerConfigProvider(
        AppContainerInterface $container,
        ConfigProvider $configProvider
    ): void {
        $container->set(ConfigProvider::class, $configProvider);
    }

    /**
     * Registers the PathsConfig instance and its alias.
     */
    private function registerPathsConfig(
        AppContainerInterface $container,
        PathsConfig $pathsConfig
    ): void {
        $container->set(PathsConfig::class, $pathsConfig);
        $container->set('paths.config', $pathsConfig);
    }

    /**
     * Registers the EnvLoader instance and its alias.
     */
    private function registerEnvLoader(
        AppContainerInterface $container,
        EnvLoader $envLoader
    ): void {
        $container->set(EnvLoader::class, $envLoader);
        $container->set('env.loader', $envLoader);
    }

    /**
     * Registers the AppConfig instance and its alias.
     */
    private function registerAppConfig(
        AppContainerInterface $container,
        AppConfig $appConfig
    ): void {
        $container->set(AppConfig::class, $appConfig);
        $container->set('app.config', $appConfig);
    }

    /**
     * Registers the DatabaseConfig instance and its alias.
     */
    private function registerDatabaseConfig(
        AppContainerInterface $container,
        DatabaseConfig $databaseConfig
    ): void {
        $container->set(DatabaseConfig::class, $databaseConfig);
        $container->set('database.config', $databaseConfig);
    }

    /**
     * Registers the DatabaseSchemaConfig instance and its alias.
     */
    private function registerDatabaseSchemaConfig(
        AppContainerInterface $container,
        DatabaseSchemaConfig $schemaConfig
    ): void {
        $container->set(DatabaseSchemaConfig::class, $schemaConfig);
        $container->set('schema.config', $schemaConfig);
    }

    /**
     * Registers the LogPathsConfig instance and its alias.
     */
    private function registerLogPathsConfig(
        AppContainerInterface $container,
        LogPathsConfig $logPathsConfig
    ): void {
        $container->set(LogPathsConfig::class, $logPathsConfig);
        $container->set('logpaths.config', $logPathsConfig);
    }

    /**
     * Registers the SessionConfig instance and its alias.
     */
    private function registerSessionConfig(
        AppContainerInterface $container,
        SessionConfig $sessionConfig
    ): void {
        $container->set(SessionConfig::class, $sessionConfig);
        $container->set('session.config', $sessionConfig);
    }

    /**
     * Registers all service bindings (global dependencies) in the container.
     *
     * @param AppContainerInterface $container
     * @param array<string, object> $serviceBindings
     * @return void
     */
    private function registerAllServiceBindings(
        AppContainerInterface $container,
        array $serviceBindings
    ): void {
        foreach ($serviceBindings as $interface => $instance) {
            $container->set($interface, $instance);
        }
    }

    /**
     * Validates the ConfigProvider instance.
     *
     * @param ConfigProvider|null $configProvider
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateConfigProvider(?ConfigProvider $configProvider): void
    {
        if (!$configProvider) {
            throw new \InvalidArgumentException(
                'ConfigProvider instance must not be null.'
            );
        }
    }

    /**
     * Validates the service bindings array.
     *
     * @param array|null $serviceBindings
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateServiceBindings(?array $serviceBindings): void
    {
        if ($serviceBindings === null) {
            throw new \InvalidArgumentException(
                'Service bindings array must not be null.'
            );
        }
        // You may expand with type-checks for each instance if needed.
    }
}
