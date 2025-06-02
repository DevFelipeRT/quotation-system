<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Connection\Drivers;

use App\Infrastructure\Database\Infrastructure\Connection\AbstractPdoConnection;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use Config\Database\DatabaseConfig;

/**
 * MySQL-specific implementation of a PDO-based database connection.
 *
 * This class encapsulates all MySQL connection logic, including DSN formatting,
 * authentication, and PDO configuration, using values from a DatabaseConfig object.
 *
 * It delegates lifecycle events to the injected EventDispatcherInterface,
 * allowing infrastructure-level listeners to observe connection success or failure.
 *
 * @internal This class should be instantiated via a factory or resolver.
 */
final class MySqlConnection extends AbstractPdoConnection
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
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
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
        return 'mysql';
    }
}
