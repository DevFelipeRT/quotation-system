<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure\Database;

use App\Infrastructure\Database\Application\Connection\DatabaseConnectionFactory;

use App\Infrastructure\Database\Infrastructure\Connection\Resolvers\DefaultDriverResolver;

use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Domain\Connection\Resolvers\DriverResolverInterface;
use App\Infrastructure\Database\Exceptions\DatabaseConnectionException;
use App\Infrastructure\Database\Infrastructure\Connection\Observers\ConnectionLoggerObserver;
use App\Infrastructure\Logging\Domain\LogEntry;
use App\Infrastructure\Logging\Domain\LogLevelEnum;
use App\Infrastructure\Logging\LoggerInterface;
use Config\Database\DatabaseConfig;
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
    private readonly DatabaseConnectionFactory $factory;
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
        $this->factory = new DatabaseConnectionFactory($this->resolver);

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
            $this->logger->log(new LogEntry(
                level: LogLevelEnum::ERROR,
                message: 'Erro ao inicializar conexão com o banco de dados.',
                context: ['erro' => $e->getMessage()],
                channel: 'infrastructure.database'
            ));

            throw new DatabaseConnectionException(
                'Kernel failed to initialize database connection.',
                0,
                [],
                $e
            );
        }
    }

    /**
     * Initializes the database connection.
     *
     * @return DatabaseConnectionInterface
     */
    private function bootConnection(): DatabaseConnectionInterface
    {
        $connection = $this->createConnection();
        $connection->connect();
        return $connection;
    }

    /**
     * Creates the database connection instance from the factory.
     *
     * @return DatabaseConnectionInterface
     */
    private function createConnection(): DatabaseConnectionInterface
    {
        return $this->factory->make($this->config, $this->observers);
    }

    /**
 * Debug-safe connection initializer with terminal output and structured logging.
 *
 * @return DatabaseConnectionInterface
 * @throws DatabaseConnectionException
 */
private function initializeSafelyDebug(): DatabaseConnectionInterface
{
    try {
        return $this->bootConnectionDebug();
    } catch (Throwable $e) {
        $this->logger->log(new LogEntry(
            level: LogLevelEnum::ERROR,
            message: 'Erro ao inicializar conexão com o banco de dados (modo debug).',
            context: ['erro' => $e->getMessage()],
            channel: 'infrastructure.database'
        ));

        throw new DatabaseConnectionException(
            'Kernel failed to initialize database connection (debug mode).',
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
            $connection = $this->createConnection();
            echo "Database connection instance created." . PHP_EOL;
        } catch (Throwable $e) {
            echo "Error creating database connection instance: " . $e->getMessage() . PHP_EOL;

            $this->logger->log(new LogEntry(
                level: LogLevelEnum::ERROR,
                message: 'Erro ao instanciar conexão com o banco de dados.',
                context: ['erro' => $e->getMessage()],
                channel: 'infrastructure.database'
            ));

            throw new DatabaseConnectionException(
                'Kernel failed to initialize database connection (debug mode).',
                0,
                [],
                $e
            );
        }

        try {
            echo "Connecting to database..." . PHP_EOL;
            $connection->connect();
            echo "Database connection established." . PHP_EOL;
        } catch (Throwable $e) {
            echo "Error connecting to database: " . $e->getMessage() . PHP_EOL;

            $this->logger->log(new LogEntry(
                level: LogLevelEnum::ERROR,
                message: 'Erro ao conectar ao banco de dados.',
                context: ['erro' => $e->getMessage()],
                channel: 'infrastructure.database'
            ));

            throw new DatabaseConnectionException(
                'Kernel failed to initialize database connection (debug mode).',
                0,
                [],
                $e
            );
        }

        return $connection;
    }

}
