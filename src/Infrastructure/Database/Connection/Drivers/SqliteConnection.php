<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Connection\Drivers;

use App\Infrastructure\Database\Connection\AbstractPdoConnection;
use Config\Database\DatabaseConfig;

/**
 * Provides a SQLite-specific implementation of the PDO database connection.
 *
 * This class constructs the DSN for SQLite and supplies metadata for local file-based databases.
 * It adheres to the observer pattern defined by AbstractPdoConnection.
 *
 * @package App\Infrastructure\Database\Connection\Drivers
 */
final class SqliteConnection extends AbstractPdoConnection
{
    /**
     * @param DatabaseConfig $config   Structured configuration for SQLite database file.
     * @param array $observers         List of ConnectionObserverInterface instances.
     */
    public function __construct(
        private readonly DatabaseConfig $config,
        array $observers = []
    ) {
        parent::__construct($observers);
    }

    /**
     * Constructs a DSN string compatible with the SQLite PDO driver.
     *
     * @return string
     */
    protected function getDsn(): string
    {
        return sprintf('sqlite:%s', $this->config->getDatabase());
    }

    /**
     * SQLite does not require a username.
     *
     * @return string
     */
    protected function getUsername(): string
    {
        return '';
    }

    /**
     * SQLite does not require a password.
     *
     * @return string
     */
    protected function getPassword(): string
    {
        return '';
    }

    /**
     * Returns the identifier for this connection's driver.
     *
     * @return string
     */
    protected function getDriverName(): string
    {
        return 'sqlite';
    }

    /**
     * Returns metadata used for logging and observability.
     *
     * @param bool $redacted Whether to obscure sensitive details.
     * @return array<string, string>
     */
    protected function getConnectionMetadata(bool $redacted): array
    {
        return [
            'database' => $redacted ? '[REDACTED]' : $this->config->getDatabase(),
        ];
    }
}
