<?php

namespace App\Kernel;

use App\Infrastructure\Database\Connection\DatabaseConnectionFactory;
use App\Infrastructure\Database\Connection\DatabaseConnectionInterface;
use App\Logging\LoggerInterface;
use Config\Database\DatabaseConfig;

/**
 * DatabaseKernel
 *
 * Coordinates and provides access to the system's active database connection.
 *
 * This kernel encapsulates the instantiation strategy for the database connection,
 * using configuration and logger inputs to produce a driver-specific implementation
 * while exposing only the standard interface for consumer components.
 */
final class DatabaseKernel
{
    private readonly DatabaseConnectionInterface $connection;

    /**
     * Constructs the database kernel using provided configuration and logger.
     *
     * @param DatabaseConfig $config Database configuration object
     * @param LoggerInterface $logger Logger instance used for connection diagnostics
     */
    public function __construct(DatabaseConfig $config, LoggerInterface $logger)
    {
        $this->connection = DatabaseConnectionFactory::make($config, $logger);
    }

    /**
     * Returns the initialized database connection.
     *
     * @return DatabaseConnectionInterface Active connection instance
     */
    public function getConnection(): DatabaseConnectionInterface
    {
        return $this->connection;
    }
}
