<?php

declare(strict_types=1);

namespace Database\Infrastructure\Connection\Drivers;

use Database\Infrastructure\Connection\AbstractPdoConnection;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use Config\Database\DatabaseConfig;

/**
 * SQLite-specific implementation of a PDO-based database connection.
 *
 * This class builds a file-based DSN from the given configuration
 * and integrates with the event dispatcher to emit domain-level
 * connection events.
 *
 * @internal Should be instantiated via a factory or resolver.
 */
final class SqliteConnection extends AbstractPdoConnection
{
    public function __construct(
        private readonly DatabaseConfig $config,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($dispatcher);
    }

    protected function createDsn(): string
    {
        return sprintf('sqlite:%s', $this->config->getDatabase());
    }

    protected function getUsername(): string
    {
        return '';
    }

    protected function getPassword(): string
    {
        return '';
    }

    protected function getOptions(): array
    {
        return $this->config->getOptions();
    }

    protected function getDriver(): string
    {
        return 'sqlite';
    }
}
