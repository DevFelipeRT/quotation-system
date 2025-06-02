<?php

declare(strict_types=1);

namespace Database\Infrastructure\Connection;

use Database\Domain\Connection\DatabaseConnectionInterface;
use Database\Domain\Connection\Events\ConnectionFailedEvent;
use Database\Domain\Connection\Events\ConnectionSucceededEvent;
use Database\Exceptions\DatabaseConnectionException;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use PDO;
use Throwable;

/**
 * Abstract base for database connections using PDO.
 *
 * This base class provides lifecycle management for PDO database connections,
 * including standardized connection logic, exception handling, and event emission
 * on success or failure. It is designed for extension by concrete driver implementations.
 *
 * Domain events emitted:
 * - ConnectionSucceededEvent
 * - ConnectionFailedEvent
 *
 * Implementing classes must provide driver-specific DSN, credentials, and options.
 */
abstract class AbstractPdoConnection implements DatabaseConnectionInterface
{
    /**
     * Internal PDO instance (lazy-loaded).
     *
     * @var PDO|null
     */
    protected ?PDO $pdo = null;

    /**
     * Dispatcher responsible for emitting domain events on connection lifecycle.
     */
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * Establishes and returns an active PDO connection.
     *
     * If the connection is already open, it is reused. On failure, a domain event is
     * dispatched and a DatabaseConnectionException is thrown.
     *
     * @return PDO
     *
     * @throws DatabaseConnectionException
     */
    public function connect(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        try {
            $dsn = $this->createDsn();
            $this->pdo = new PDO(
                dsn: $dsn,
                username: $this->getUsername(),
                password: $this->getPassword(),
                options: $this->getOptions()
            );

            $this->eventDispatcher->dispatch(new ConnectionSucceededEvent(
                driver: $this->getDriver(),
                message: 'Database connection established successfully.',
                metadata: ['dsn' => $dsn]
            ));

            return $this->pdo;
        } catch (Throwable $e) {
            $this->eventDispatcher->dispatch(new ConnectionFailedEvent(
                driver: $this->getDriver(),
                error: $e->getMessage(),
                metadata: ['exception' => $e]
            ));

            throw new DatabaseConnectionException(
                message: 'Failed to connect to database.',
                code: 0,
                context: [],
                previous: $e
            );
        }
    }

    /**
     * Constructs the DSN (Data Source Name) string specific to the driver.
     *
     * @return string
     */
    abstract protected function createDsn(): string;

    /**
     * Returns the username for the database connection.
     *
     * @return string
     */
    abstract protected function getUsername(): string;

    /**
     * Returns the password for the database connection.
     *
     * @return string
     */
    abstract protected function getPassword(): string;

    /**
     * Returns driver-specific PDO connection options.
     *
     * @return array
     */
    abstract protected function getOptions(): array;

    /**
     * Returns the identifier string for the current driver (e.g., 'mysql', 'pgsql').
     *
     * @return string
     */
    abstract protected function getDriver(): string;
}
