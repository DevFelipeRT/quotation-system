<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure\Database;

use App\Infrastructure\Database\Application\Connection\DatabaseConnectionFactory;
use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Domain\Connection\Resolvers\DriverResolverInterface;
use App\Infrastructure\Database\Exceptions\DatabaseConnectionException;
use App\Infrastructure\Database\Infrastructure\Connection\Resolvers\DefaultDriverResolver;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use Config\Database\DatabaseConfig;
use Throwable;

/**
 * Bootstraps and exposes a fully initialized database connection.
 *
 * This kernel coordinates all required components to resolve and connect
 * a database driver using configuration and event dispatching infrastructure.
 */
final class DatabaseConnectionKernel
{
    private readonly DatabaseConnectionInterface $connection;

    public function __construct(
        private readonly DatabaseConfig $config,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly bool $debugMode = false,
        private readonly ?DriverResolverInterface $resolver = null
    ) {
        $this->connection = $this->initializeConnection();
    }

    /**
     * Returns the resolved and connected database driver.
     *
     * @return DatabaseConnectionInterface
     */
    public function getConnection(): DatabaseConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Handles safe connection initialization with exception wrapping.
     *
     * @return DatabaseConnectionInterface
     * @throws DatabaseConnectionException
     */
    private function initializeConnection(): DatabaseConnectionInterface
    {
        try {
            $this->debugOutput("Creating database connection...");
            $connection = $this->buildConnection();
            $this->debugOutput("Connection established.");
            return $connection;
        } catch (Throwable $e) {
            $this->debugOutput("Connection failed:");
            $this->debugOutput((string) $e);
            throw new DatabaseConnectionException(
                'DatabaseConnectionKernel failed to initialize connection.',
                0,
                [],
                $e
            );
        }
    }

    /**
     * Uses a factory and optional resolver to build the connection.
     *
     * @return DatabaseConnectionInterface
     */
    private function buildConnection(): DatabaseConnectionInterface
    {
        $driverResolver = $this->resolver ?? new DefaultDriverResolver();

        $factory = new DatabaseConnectionFactory(
            resolver: $driverResolver,
            dispatcher: $this->dispatcher
        );

        return $factory->make($this->config);
    }

    /**
     * Outputs debug messages if debug mode is enabled.
     *
     * @param string $message
     * @return void
     */
    private function debugOutput(string $message): void
    {
        if ($this->debugMode) {
            echo "[Database Debug] {$message}" . PHP_EOL;
        }
    }
}
