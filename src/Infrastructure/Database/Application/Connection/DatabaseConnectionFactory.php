<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Application\Connection;

use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Domain\Connection\Observers\ConnectionObserverInterface;
use App\Infrastructure\Database\Domain\Connection\Resolvers\DriverResolverInterface;
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
    private DriverResolverInterface $resolver;

    /**
     * @param DriverResolverInterface $resolver
     */
    public function __construct(DriverResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Creates a connection instance based on the configuration and resolved driver.
     *
     * @param DatabaseConfig $config The structured database configuration object.
     * @param ConnectionObserverInterface[] $observers Optional list of lifecycle observers.
     * @return DatabaseConnectionInterface
     *
     * @throws InvalidArgumentException If any observer is invalid or resolved class is invalid.
     */
    public function make(
        DatabaseConfig $config,
        array $observers = []
    ): DatabaseConnectionInterface {
        $this->assertValidObservers($observers);

        $driver = $config->getDriver();
        $class  = $this->resolver->resolve($driver);

        if (!is_subclass_of($class, DatabaseConnectionInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Resolved class "%s" must implement DatabaseConnectionInterface.',
                $class
            ));
        }

        $instance = new $class($config, $observers);
        return $instance;
    }

    /**
     * Validates that all observers implement the expected interface.
     *
     * @param array $observers
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function assertValidObservers(array $observers): void
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
}
