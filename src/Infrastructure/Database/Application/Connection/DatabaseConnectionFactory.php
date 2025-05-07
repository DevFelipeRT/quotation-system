<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Connection;

use App\Infrastructure\Database\Connection\Observers\ConnectionObserverInterface;
use App\Infrastructure\Database\Connection\Resolvers\DriverResolverInterface;
use App\Infrastructure\Database\Exceptions\UnsupportedDriverException;
use Config\Database\DatabaseConfig;
use InvalidArgumentException;

/**
 * Factory responsible for instantiating a database connection
 * based on the configured driver and optional lifecycle observers.
 *
 * Delegates the responsibility of resolving the concrete connection
 * class to a driver resolver component, improving modularity and extensibility.
 */
final class DatabaseConnectionFactory
{
    /**
     * @var DriverResolverInterface
     */
    private static ?DriverResolverInterface $resolver = null;

    /**
     * Sets the resolver instance used to determine the connection class per driver.
     *
     * This must be called before invoking `make()`.
     *
     * @param DriverResolverInterface $resolver
     * @return void
     */
    public static function useResolver(DriverResolverInterface $resolver): void
    {
        self::$resolver = $resolver;
    }

    /**
     * Creates a connection instance based on the configuration and resolved driver.
     *
     * @param DatabaseConfig $config The structured database configuration object.
     * @param ConnectionObserverInterface[] $observers Optional list of lifecycle observers.
     * @return DatabaseConnectionInterface
     *
     * @throws UnsupportedDriverException If the configured driver is not recognized.
     * @throws InvalidArgumentException If any observer is invalid or resolver was not set.
     */
    public static function make(
        DatabaseConfig $config,
        array $observers = []
    ): DatabaseConnectionInterface 
    {
        self::assertResolverIsSet();
        self::assertValidObservers($observers);

        $driver = $config->getDriver();
        $class  = self::$resolver->resolve($driver);

        return new $class($config, $observers);
    }

    /**
     * Validates that all observers implement the expected interface.
     *
     * @param array $observers
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private static function assertValidObservers(array $observers): void
    {
        foreach ($observers as $observer) {
            if (!$observer instanceof ConnectionObserverInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Observer must implement ConnectionObserverInterface, %s given.',
                    is_object($observer) ? get_class($observer) : gettype($observer)
                ));
            }
        }
    }

    /**
     * Ensures that a driver resolver has been set before use.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private static function assertResolverIsSet(): void
    {
        if (self::$resolver === null) {
            throw new InvalidArgumentException('No driver resolver has been configured for DatabaseConnectionFactory.');
        }
    }
}
