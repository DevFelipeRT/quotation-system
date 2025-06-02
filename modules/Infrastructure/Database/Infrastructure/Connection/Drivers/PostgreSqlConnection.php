<?php

declare(strict_types=1);

namespace Database\Infrastructure\Connection\Drivers;

use Database\Infrastructure\Connection\AbstractPdoConnection;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use Config\Database\DatabaseConfig;

/**
 * PostgreSQL-specific implementation of a PDO-based database connection.
 *
 * This class encapsulates the construction of a PostgreSQL DSN,
 * credential retrieval, and PDO configuration. It integrates with
 * the application's event dispatching infrastructure to emit domain
 * events on connection success or failure.
 *
 * @internal Should be instantiated via a resolver or connection factory.
 */
final class PostgreSqlConnection extends AbstractPdoConnection
{
    public function __construct(
        private readonly DatabaseConfig $config,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($dispatcher);
    }

    protected function createDsn(): string
    {
        return sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $this->config->getHost(),
            $this->config->getPort(),
            $this->config->getDatabase()
        );
    }

    protected function getUsername(): string
    {
        return $this->config->getUsername();
    }

    protected function getPassword(): string
    {
        return $this->config->getPassword();
    }

    protected function getOptions(): array
    {
        return $this->config->getOptions();
    }

    protected function getDriver(): string
    {
        return 'pgsql';
    }
}
