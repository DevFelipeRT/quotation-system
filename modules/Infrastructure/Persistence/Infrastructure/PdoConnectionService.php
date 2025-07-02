<?php

declare(strict_types=1);

namespace Persistence\Infrastructure;

use PDO;
use Throwable;
use Persistence\Infrastructure\Contract\DatabaseConnectionInterface;
use Persistence\Domain\Contract\DatabaseCredentialsInterface;
use Persistence\Domain\Event\ConnectionSucceededEvent;
use Persistence\Domain\Event\ConnectionFailedEvent;
use Persistence\Infrastructure\Exceptions\DatabaseConnectionException;
use Event\EventRecording\EventRecording;

/**
 * Secure and final implementation of a PDO-based database connection.
 *
 * This class encapsulates all connection orchestration responsibilities,
 * including credential resolution, lazy connection instantiation,
 * and lifecycle event recording.
 *
 * Events recorded (accessible via releaseEvents()):
 * - ConnectionSucceededEvent
 * - ConnectionFailedEvent
 *
 * @final
 * @immutable
 */
final class PdoConnectionService implements DatabaseConnectionInterface
{
    use EventRecording;

    /**
     * Lazy-initialized PDO instance.
     */
    private ?PDO $pdo = null;

    public function __construct(
        private readonly DatabaseCredentialsInterface $credentials
    ) {}

    /**
     * Establishes and returns an active PDO connection.
     *
     * On first call, creates a new connection using credentials.
     * On failure, records ConnectionFailedEvent and rethrows a wrapped exception.
     *
     * @return PDO
     * @throws DatabaseConnectionException
     */
    public function connect(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $dsn    = $this->credentials->getDsn();
        $driver = $this->credentials->getDriverName();

        try {
            $this->pdo = new PDO(
                dsn: $dsn,
                username: $this->credentials->getUsername() ?? '',
                password: $this->credentials->getPassword() ?? '',
                options: $this->credentials->getOptions()
            );

            $this->recordEvent(
                new ConnectionSucceededEvent($driver)
            );

            return $this->pdo;

        } catch (Throwable $e) {
            $this->recordEvent(
                new ConnectionFailedEvent($driver, $e)
            );

            throw new DatabaseConnectionException(
                message: 'Failed to connect to database.',
                code: 0,
                context: ['Driver' => "{$driver}"],
                previous: $e
            );
        }
    }

    /**
     * Returns the name of the current driver (e.g., 'mysql', 'pgsql', 'sqlite').
     */
    public function getDriver(): string
    {
        return $this->credentials->getDriverName();
    }

    /**
     * Indicates whether a PDO instance has already been initialized.
     */
    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }
}
