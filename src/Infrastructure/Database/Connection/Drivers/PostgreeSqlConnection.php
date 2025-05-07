<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Connection\Drivers;

use App\Infrastructure\Database\Connection\AbstractPdoConnection;
use Config\Database\DatabaseConfig;

/**
 * Provides a PostgreSQL-specific implementation of the PDO database connection.
 *
 * This class builds the DSN string for the pgsql driver and retrieves connection
 * credentials and metadata from configuration. It integrates with the standardized
 * observer lifecycle defined in AbstractPdoConnection.
 *
 * @package App\Infrastructure\Database\Connection\Drivers
 */
final class PostgreSqlConnection extends AbstractPdoConnection
{
    /**
     * @param DatabaseConfig $config   Structured configuration for PostgreSQL connection parameters.
     * @param array $observers         List of ConnectionObserverInterface instances.
     */
    public function __construct(
        private readonly DatabaseConfig $config,
        array $observers = []
    ) {
        parent::__construct($observers);
    }

    /**
     * Constructs a DSN string compatible with the PostgreSQL PDO driver.
     *
     * @return string
     */
    protected function getDsn(): string
    {
        return sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $this->config->getHost(),
            $this->config->getPort(),
            $this->config->getDatabase()
        );
    }

    /**
     * Retrieves the username from the configuration.
     *
     * @return string
     */
    protected function getUsername(): string
    {
        return $this->config->getUsername();
    }

    /**
     * Retrieves the password from the configuration.
     *
     * @return string
     */
    protected function getPassword(): string
    {
        return $this->config->getPassword();
    }

    /**
     * Returns the identifier for this connection's driver.
     *
     * @return string
     */
    protected function getDriverName(): string
    {
        return 'pgsql';
    }

    /**
     * Returns metadata used for logging and observability.
     *
     * @param bool $redacted Whether to obscure sensitive details.
     * @return array<string, string|int>
     */
    protected function getConnectionMetadata(bool $redacted): array
    {
        return [
            'host' => $redacted ? '[REDACTED]' : $this->config->getHost(),
            'port' => $redacted ? '[REDACTED]' : $this->config->getPort(),
            'database' => $redacted ? '[REDACTED]' : $this->config->getDatabase(),
        ];
    }
}
