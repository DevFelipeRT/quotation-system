<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Connection;

use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Domain\Connection\Events\ConnectionFailedEvent;
use App\Infrastructure\Database\Domain\Connection\Events\ConnectionSucceededEvent;
use App\Infrastructure\Database\Domain\Connection\Observers\ConnectionObserverInterface;
use App\Infrastructure\Database\Exceptions\DatabaseConnectionException;
use PDO;
use PDOException;

/**
 * Base abstract class for specialized PDO-based database connections.
 *
 * This class standardizes the creation and instrumentation of PDO connections,
 * delegating driver-specific behavior to subclasses. It provides lifecycle
 * event notification, exception handling, and default option management.
 *
 * Subclasses must implement driver-specific DSN, credentials and metadata.
 */
abstract class AbstractPdoConnection implements DatabaseConnectionInterface
{
    /**
     * @var ConnectionObserverInterface[] List of registered lifecycle observers.
     */
    private readonly array $observers;

    /**
     * @param ConnectionObserverInterface[] $observers Observers to be notified on success or failure.
     * @throws \InvalidArgumentException If any observer does not implement the expected interface.
     */
    public function __construct(array $observers = [])
    {
        foreach ($observers as $observer) {
            if (!$observer instanceof ConnectionObserverInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Observer must implement ConnectionObserverInterface, %s given.',
                    is_object($observer) ? get_class($observer) : gettype($observer)
                ));
            }
        }

        $this->observers = $observers;
    }

    /**
     * Creates and returns a configured PDO instance, notifying observers.
     *
     * @return PDO
     * @throws DatabaseConnectionException On failure to connect.
     */
    final public function connect(): PDO
    {
        try {
            $pdo = new PDO(
                $this->getDsn(),
                $this->getUsername(),
                $this->getPassword(),
                $this->getOptions()
            );

            $this->notify(new ConnectionSucceededEvent(
                driver: $this->getDriverName(),
                metadata: $this->getConnectionMetadata(true)
            ));

            return $pdo;
        } catch (PDOException $e) {
            $this->notify(new ConnectionFailedEvent(
                driver: $this->getDriverName(),
                error: $e->getMessage(),
                metadata: $this->getConnectionMetadata(false)
            ));

            throw new DatabaseConnectionException(
                'Unable to establish database connection.',
                0,
                $this->getConnectionMetadata(false),
                $e
            );
        }
    }

    /**
     * Returns standard PDO options used by all connections.
     *
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => $this->resolveTimeout(),
        ];
    }

    /**
     * Resolves the connection timeout.
     *
     * @return int
     */
    protected function resolveTimeout(): int
    {
        $timeout = getenv('DB_TIMEOUT');
        return is_numeric($timeout) ? (int) $timeout : 5;
    }

    /**
     * Dispatches a lifecycle event to all registered observers.
     *
     * @param object $event Either ConnectionSucceededEvent or ConnectionFailedEvent.
     * @return void
     */
    protected function notify(object $event): void
    {
        foreach ($this->observers as $observer) {
            $observer->handle($event);
        }
    }

    /**
     * Subclasses must return a valid PDO DSN string.
     */
    abstract protected function getDsn(): string;

    /**
     * Subclasses must return the database username.
     */
    abstract protected function getUsername(): string;

    /**
     * Subclasses must return the database password.
     */
    abstract protected function getPassword(): string;

    /**
     * Subclasses must return the driver name (e.g. mysql, pgsql).
     */
    abstract protected function getDriverName(): string;

    /**
     * Subclasses must return connection metadata for observability.
     *
     * @param bool $redacted Whether to redact sensitive values.
     * @return array<string, string|int>
     */
    abstract protected function getConnectionMetadata(bool $redacted): array;
}