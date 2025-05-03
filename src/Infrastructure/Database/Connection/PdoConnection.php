<?php

namespace App\Infrastructure\Database\Connection;

use App\Infrastructure\Logging\LogEntry;
use App\Infrastructure\Logging\LogLevelEnum;
use App\Interfaces\Infrastructure\LoggerInterface;
use App\Shared\Exceptions\DatabaseConnectionException;
use Config\Database\DatabaseConfig;
use PDO;
use PDOException;
use RuntimeException;

/**
 * PdoConnection
 *
 * Provides a PDO-based implementation of the database connection interface.
 * Reads parameters securely from configuration and logs diagnostic information.
 */
final class PdoConnection implements DatabaseConnectionInterface
{
    private const LOG_CHANNEL = 'database';

    public function __construct(
        private readonly DatabaseConfig $config,
        private readonly LoggerInterface $logger
    ) {}

    public function connect(): PDO
    {
        try {
            $pdo = new PDO(
                $this->buildDsn(),
                $this->config->username(),
                $this->config->password(),
                $this->pdoOptions()
            );

            $this->logger->log(new LogEntry(
                level: LogLevelEnum::INFO,
                message: 'Database connection established successfully.',
                context: $this->safeContext(),
                channel: self::LOG_CHANNEL
            ));

            return $pdo;
        } catch (PDOException $e) {
            $context = $this->unsafeContext($e->getMessage());

            $this->logger->log(new LogEntry(
                level: LogLevelEnum::ERROR,
                message: 'Failed to connect to the database.',
                context: $context,
                channel: self::LOG_CHANNEL
            ));

            throw new DatabaseConnectionException(
                'Unable to establish database connection.',
                0,
                $context,
                $e
            );
        }
    }

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
            default => throw new RuntimeException('Unsupported database driver: ' . $this->config->driver())
        };
    }

    private function pdoOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => $this->resolveTimeout()
        ];
    }

    private function resolveTimeout(): int
    {
        $timeout = getenv('DB_TIMEOUT');
        return is_numeric($timeout) ? (int) $timeout : 5;
    }

    private function safeContext(): array
    {
        return [
            'driver' => $this->config->driver(),
            'host' => '[REDACTED]',
            'port' => '[REDACTED]',
            'database' => '[REDACTED]'
        ];
    }

    private function unsafeContext(string $error): array
    {
        return [
            'driver' => $this->config->driver(),
            'host' => $this->config->host(),
            'port' => $this->config->port(),
            'database' => $this->config->database(),
            'error' => $error
        ];
    }
}
