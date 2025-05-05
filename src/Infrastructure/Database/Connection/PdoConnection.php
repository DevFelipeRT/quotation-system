<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Connection;

use App\Infrastructure\Database\Events\ConnectionSucceededEvent;
use App\Infrastructure\Database\Events\ConnectionFailedEvent;
use App\Infrastructure\Database\Exceptions\DatabaseConnectionException;
use Config\Database\DatabaseConfig;
use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO-based implementation of a database connection.
 *
 * Responsible for DSN construction and initialization of the PDO connection.
 * Connection lifecycle events are dispatched to registered observers.
 */
final class PdoConnection implements DatabaseConnectionInterface
{
    /**
     * @param DatabaseConfig $config Database connection settings.
     * @param ConnectionObserverInterface[] $observers Registered observers to notify on connection lifecycle events.
     */
    public function __construct(
        private readonly DatabaseConfig $config,
        private readonly array $observers = []
    ) {}

    /**
     * Establishes a PDO connection and notifies observers about the outcome.
     *
     * @return PDO
     * @throws DatabaseConnectionException If the connection attempt fails.
     */
    public function connect(): PDO
    {
        try {
            $pdo = new PDO(
                $this->buildDsn(),
                $this->config->username(),
                $this->config->password(),
                $this->pdoOptions()
            );

            $this->notify(new ConnectionSucceededEvent(
                driver: $this->config->driver(),
                metadata: $this->safeMetadata()
            ));

            return $pdo;
        } catch (PDOException $e) {
            $this->notify(new ConnectionFailedEvent(
                driver: $this->config->driver(),
                error: $e->getMessage(),
                metadata: $this->failureMetadata()
            ));

            throw new DatabaseConnectionException(
                'Unable to establish database connection.',
                0,
                $this->failureMetadata(),
                $e
            );
        }
    }

    /**
     * Constructs the DSN based on the configured database driver.
     *
     * @return string
     */
    private function buildDsn(): string
    {
        return match ($this->config->driver()) {
            'mysql' => sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $this->config->host(),
                $this->config->port(),
                $this->config->database()
            ),
            'pgsql' => sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $this->config->host(),
                $this->config->port(),
                $this->config->database()
            ),
            'sqlite' => sprintf('sqlite:%s', $this->config->database()),
            default => throw new RuntimeException('Unsupported driver: ' . $this->config->driver())
        };
    }

    /**
     * Defines default options for the PDO instance.
     *
     * @return array
     */
    private function pdoOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => $this->resolveTimeout(),
        ];
    }

    /**
     * Resolves the connection timeout from environment variables.
     *
     * @return int
     */
    private function resolveTimeout(): int
    {
        $timeout = getenv('DB_TIMEOUT');
        return is_numeric($timeout) ? (int) $timeout : 5;
    }

    /**
     * Notifies all observers of a given connection event.
     *
     * @param object $event
     * @return void
     */
    private function notify(object $event): void
    {
        foreach ($this->observers as $observer) {
            $observer->handle($event);
        }
    }

    /**
     * Returns anonymized metadata for public observability.
     *
     * @return array
     */
    private function safeMetadata(): array
    {
        return [
            'host' => '[REDACTED]',
            'port' => '[REDACTED]',
            'database' => '[REDACTED]'
        ];
    }

    /**
     * Returns full diagnostic metadata for error events.
     *
     * @return array
     */
    private function failureMetadata(): array
    {
        return [
            'host' => $this->config->host(),
            'port' => $this->config->port(),
            'database' => $this->config->database()
        ];
    }
}
