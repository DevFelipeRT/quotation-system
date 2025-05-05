<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Connection;

use App\Infrastructure\Database\Connection\Observers\ConnectionObserverInterface;
use App\Infrastructure\Database\Connection\Events\ConnectionSucceededEvent;
use App\Infrastructure\Database\Connection\Events\ConnectionFailedEvent;
use App\Infrastructure\Database\Exceptions\DatabaseConnectionException;
use Config\Database\DatabaseConfig;
use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO-based implementation of a database connection.
 * 
 * This class is responsible for establishing a database connection using PDO,
 * dispatching lifecycle events to registered observers, and applying DSN/configuration logic.
 */
final class PdoConnection implements DatabaseConnectionInterface
{
    /**
     * @var ConnectionObserverInterface[] $observers
     */
    private readonly array $observers;

    /**
     * @param DatabaseConfig $config Database connection configuration.
     * @param ConnectionObserverInterface[] $observers Observers to notify on lifecycle events.
     * 
     * @throws \InvalidArgumentException If any observer does not implement the expected interface.
     */
    public function __construct(
        private readonly DatabaseConfig $config,
        array $observers = []
    ) {
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
     * Attempts to establish a PDO connection, notifying observers of success or failure.
     *
     * @return PDO
     * 
     * @throws DatabaseConnectionException When the PDO connection fails.
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
     * Builds the DSN string based on the configured driver and connection settings.
     *
     * @return string
     * 
     * @throws RuntimeException If the driver is not supported.
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
     * Defines the default options for the PDO instance.
     *
     * @return array<string, mixed>
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
     * Determines the connection timeout using environment variables or default fallback.
     *
     * @return int
     */
    private function resolveTimeout(): int
    {
        $timeout = getenv('DB_TIMEOUT');
        return is_numeric($timeout) ? (int) $timeout : 5;
    }

    /**
     * Dispatches a connection lifecycle event to all registered observers.
     *
     * @param ConnectionSucceededEvent|ConnectionFailedEvent $event
     * @return void
     */
    private function notify(object $event): void
    {
        foreach ($this->observers as $observer) {
            $observer->handle($event);
        }
    }

    /**
     * Returns anonymized metadata for logging success without leaking sensitive data.
     *
     * @return array<string, string>
     */
    private function safeMetadata(): array
    {
        return [
            'host'     => '[REDACTED]',
            'port'     => '[REDACTED]',
            'database' => '[REDACTED]',
        ];
    }

    /**
     * Returns full metadata used for diagnostics on connection failure.
     *
     * @return array<string, string|int>
     */
    private function failureMetadata(): array
    {
        return [
            'host'     => $this->config->host(),
            'port'     => $this->config->port(),
            'database' => $this->config->database(),
        ];
    }
}
