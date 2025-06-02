<?php

declare(strict_types=1);

namespace Database\Application\Connection;

use Database\Domain\Connection\DatabaseConnectionInterface;
use Database\Domain\Connection\Resolvers\DriverResolverInterface;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use Config\Database\DatabaseConfig;
use InvalidArgumentException;

/**
 * Factory responsible for instantiating and initializing a database connection
 * using the configured driver and event dispatching infrastructure.
 */
final class DatabaseConnectionFactory
{
    public function __construct(
        private readonly DriverResolverInterface $resolver,
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    /**
     * Creates and connects a concrete database driver instance.
     *
     * @param DatabaseConfig $config Structured connection parameters.
     * @return DatabaseConnectionInterface Connected driver instance.
     *
     * @throws InvalidArgumentException
     */
    public function make(DatabaseConfig $config): DatabaseConnectionInterface
    {
        $class = $this->resolver->resolve($config->getDriver());

        if (!is_subclass_of($class, DatabaseConnectionInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Resolved class "%s" must implement DatabaseConnectionInterface.',
                $class
            ));
        }

        /** @var DatabaseConnectionInterface $instance */
        $instance = new $class($config, $this->dispatcher);
        $instance->connect();

        return $instance;
    }
}
