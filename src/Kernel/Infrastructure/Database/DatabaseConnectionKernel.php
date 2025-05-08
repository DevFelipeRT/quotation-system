<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure\Database;

use Throwable;

/**
 * Class DatabaseConnectionKernel
 *
 * Coordinates the safe initialization and access to a database connection using
 * the system's configured driver, logger, and environment settings.
 *
 * This kernel is responsible for:
 * - Registering the appropriate driver resolver.
 * - Constructing and activating the database connection.
 * - Handling connection exceptions with structured error reporting.
 * - Supporting a debug mode for verbose connection tracing.
 *
 * @package App\Kernel\Database
 */
final class DatabaseConnectionKernel
{
    private readonly DatabaseConnectionInterface $connection;
    private readonly DriverResolverInterface $resolver;
    private readonly DatabaseConfig $config;
    private readonly LoggerInterface $logger;
    /** @var array */
    private array $observers;

    /**
     * Initializes the kernel and attempts to safely establish a database connection.
     *
     * @param DatabaseConfig   $config   Configuration object with driver and credentials.
     * @param LoggerInterface  $logger   Logging interface for observing connection events.
     * @param bool             $debugMode Enables verbose output for diagnostics.
     *
     * @throws DatabaseConnectionException On failure to create or activate the connection.
     */
    public function __construct(DatabaseConfig $config, LoggerInterface $logger, bool $debugMode = false)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->observers = [new ConnectionLoggerObserver($this->logger)];
        $this->resolver = new DefaultDriverResolver();

        $this->registerResolver();

        $this->connection = $debugMode
            ? $this->initializeSafelyDebug()
            : $this->initializeSafely();
    }

    /**
     * Provides access to the resolved and connected database interface.
     *
     * @return DatabaseConnectionInterface
     */
    public function getConnection(): DatabaseConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Registers the selected driver resolver within the factory.
     *
     * @return void
     */
    private function registerResolver(): void
    {
        DatabaseConnectionFactory::useResolver($this->resolver);
    }

    /**
     * Attempts to establish the connection and capture any failures.
     *
     * @return DatabaseConnectionInterface
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
     * Constructs and connects the database interface.
     *
     * @return DatabaseConnectionInterface
     */
    private function bootConnection(): DatabaseConnectionInterface
    {
        $connection = DatabaseConnectionFactory::make($this->config, $this->observers);
        $connection->connect();
        return $connection;
    }

    /**
     * Debug-safe connection initializer with terminal output.
     *
     * @return DatabaseConnectionInterface
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
     * Verbosely builds and connects to the database, logging to the terminal.
     *
     * @return DatabaseConnectionInterface
     * @throws DatabaseConnectionException
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
