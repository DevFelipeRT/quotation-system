<?php

declare(strict_types=1);

namespace Container\Infrastructure;

use Container\Domain\Contracts\ContainerInterface;
use Container\Domain\Contracts\ServiceProviderInterface;

/**
 * Class ContainerKernel
 *
 * Acts as the primary orchestrator for building and managing the application's
 * dependency injection container using a fluent and configurable builder.
 *
 * This kernel allows external modules or the application bootstrap to register
 * service providers, apply custom scopes, or define explicit bindings via the
 * `ContainerBuilder`. Once built, the container is stored as a singleton.
 *
 * @package Container\Infrastructure
 */
final class ContainerKernel
{
    /**
     * Singleton instance of the container.
     *
     * @var ContainerInterface|null
     */
    private static ?ContainerInterface $container = null;

    /**
     * Singleton instance of the builder used before container is finalized.
     *
     * @var ContainerBuilder|null
     */
    private static ?ContainerBuilder $builder = null;

    /**
     * Indicates whether the container has already been built.
     *
     * @var bool
     */
    private static bool $isBuilt = false;

    /**
     * Retrieves the builder instance, used to register providers or bindings
     * before final build.
     *
     * @return ContainerBuilder
     */
    public static function builder(): ContainerBuilder
    {
        if (self::$builder === null) {
            self::$builder = new ContainerBuilder();
        }

        return self::$builder;
    }

    /**
     * Registers a service provider to be included during container build.
     *
     * @param ServiceProviderInterface $provider
     * @return void
     */
    public static function registerProvider(ServiceProviderInterface $provider): void
    {
        self::builder()->addProvider($provider);
    }

    /**
     * Builds and returns the global container instance.
     *
     * This method finalizes the configuration using the builder,
     * applies all registered bindings and providers, and returns
     * the resulting container instance.
     *
     * @return ContainerInterface
     */
    public static function build(): ContainerInterface
    {
        if (self::$isBuilt && self::$container !== null) {
            return self::$container;
        }

        self::$container = self::builder()->build();
        self::$isBuilt = true;

        // Optionally: destroy builder after build to free memory
        self::$builder = null;

        return self::$container;
    }

    /**
     * Returns the already built container, or builds it on demand.
     *
     * @return ContainerInterface
     */
    public static function container(): ContainerInterface
    {
        return self::build();
    }

    /**
     * Indicates whether the container has already been finalized.
     *
     * @return bool
     */
    public static function isReady(): bool
    {
        return self::$isBuilt;
    }
}
