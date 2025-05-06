<?php

namespace App\Kernel;

use App\Infrastructure\Database\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Connection\DatabaseConnectionFactory;
use App\Infrastructure\Database\Connection\Observers\ConnectionLoggerObserver;
use App\Infrastructure\Database\Connection\Resolvers\DefaultDriverResolver;
use App\Infrastructure\Database\Connection\Resolvers\DriverResolverInterface;
use App\Infrastructure\Database\Exceptions\DatabaseConnectionException;
use App\Logging\LoggerInterface;
use Config\Database\DatabaseConfig;
use Throwable;

/**
 * DatabaseKernel
 *
 * Initializes and exposes the active database connection for this module.
 */
final class DatabaseKernel
{
    private readonly DatabaseConnectionInterface $connection;
    private readonly DriverResolverInterface $resolver;
    private readonly DatabaseConfig $config;
    private readonly LoggerInterface $logger;
    private readonly array $observers;

    /**
     * @param DatabaseConfig   $config
     * @param LoggerInterface  $logger
     *
     * @throws DatabaseConnectionException
     */
    public function __construct(DatabaseConfig $config, LoggerInterface $logger, $debugMode = false)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->observers = [new ConnectionLoggerObserver($this->logger)];
        $this->resolver = new DefaultDriverResolver($config->getDriver());

        $this->registerResolver();

        if ($debugMode) {
            $this->connection = $this->initializeSafelyDebug();
            return;
        }
        $this->connection = $this->initializeSafely();
    }

    /**
     * Returns the active connection interface.
     *
     * @return DatabaseConnectionInterface
     */
    public function connection(): DatabaseConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Registers the default driver resolver.
     *
     * @return void
     */
    private function registerResolver(): void
    {
        DatabaseConnectionFactory::useResolver($this->resolver);
    }

    /**
     * Safely initializes the database connection, handling any exception.
     *
     * @return DatabaseConnectionInterface
     *
     * @throws DatabaseConnectionException
     */
    private function initializeSafely(): DatabaseConnectionInterface
    {
        try {
            return $this->bootConnection();
        } catch (Throwable $e) {
            throw new DatabaseConnectionException(
                'Kernel failed to initialize database connection.',
                0,
                [],
                $e
            );
        }
    }

    /**
     * Builds and activates the connection using internal configuration.
     *
     * @return DatabaseConnectionInterface
     */
    private function bootConnection(): DatabaseConnectionInterface
    {;
        $connection = DatabaseConnectionFactory::make($this->config, $this->observers);
        $connection->connect();
        return $connection;
    }

    // Debugging

    /**
     * Safely initializes the database connection, handling any exception.
     *
     * @return DatabaseConnectionInterface
     *
     * @throws DatabaseConnectionException
     */
    private function initializeSafelyDebug(): DatabaseConnectionInterface
    {
        try {
            return $this->bootConnectionDebug();
        } catch (Throwable $e) {
            throw new DatabaseConnectionException(
                'Kernel failed to initialize database connection.',
                0,
                [],
                $e
            );
        }
    }

    /**
     * Builds and activates the connection using internal configuration.
     *
     * @return DatabaseConnectionInterface
     */
    private function bootConnectionDebug(): DatabaseConnectionInterface
    {
        echo "Initializing database connection..." . PHP_EOL;
        try {
            echo "Creating database connection instance..." . PHP_EOL;
            $connection = DatabaseConnectionFactory::make($this->config, $this->observers);
            echo "Database connection instance created." . PHP_EOL;
        } catch (\Throwable $e) {
            echo "Error creating database connection instance: " . $e->getMessage() . PHP_EOL;
            throw new DatabaseConnectionException(
                'Kernel failed to initialize database connection.',
                0,
                [],
                $e
            );
        }

        try {
            echo "Connecting to database..." . PHP_EOL;
            $connection->connect();
            echo "Database connection established." . PHP_EOL;
        } catch (\Throwable $e) {
            echo "Error connecting to database: " . $e->getMessage() . PHP_EOL;
            throw new DatabaseConnectionException(
                'Kernel failed to initialize database connection.',
                0,
                [],
                $e
            );
        }
        
        return $connection;
    }
}
