<?php

namespace App\Kernel;

use App\Infrastructure\Database\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\DatabaseFactory;
use App\Interfaces\Infrastructure\LoggerInterface;
use Config\Container\ConfigContainer;

/**
 * DatabaseKernel
 *
 * Initializes the database connection strategy based on the environment.
 * This kernel encapsulates the instantiation logic of the connection
 * and exposes only the connection interface to upstream components.
 */
final class DatabaseKernel
{
    private readonly DatabaseConnectionInterface $connection;

    /**
     * Constructs the database kernel with necessary configuration and logging.
     *
     * @param ConfigContainer   $config Application configuration container
     * @param LoggerInterface   $logger Application logger for diagnostics
     */
    public function __construct(ConfigContainer $config, LoggerInterface $logger)
    {
        $this->connection = DatabaseFactory::make($config, $logger);
    }

    /**
     * Returns the database connection interface.
     *
     * @return DatabaseConnectionInterface
     */
    public function connection(): DatabaseConnectionInterface
    {
        return $this->connection;
    }
}
