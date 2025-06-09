<?php

declare(strict_types=1);

namespace Persistence\Domain\ValueObject;

use InvalidArgumentException;
use Persistence\Domain\Contract\DatabaseCredentialsInterface;
use Persistence\Domain\Support\CredentialsSecurity;

/**
 * Immutable Value Object representing PostgreSQL connection credentials.
 *
 * This class encapsulates all required data for connecting to a PostgreSQL database,
 * while applying security best practices through the CredentialsSecurity trait.
 *
 * @immutable
 */
final class PostgreSqlCredentials implements DatabaseCredentialsInterface
{
    use CredentialsSecurity;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $database,
        private readonly string $username,
        private readonly DatabaseSecret $password,
        private readonly array $options = []
    ) {
        $this->assertValidPort($port);
        $this->assertNotEmpty($host, 'host');
        $this->assertNotEmpty($database, 'database');
        $this->assertNotEmpty($username, 'username');
    }

    public function getDsn(): string
    {
        return sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $this->host,
            $this->port,
            $this->database
        );
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password->reveal();
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getDriverName(): string
    {
        return 'pgsql';
    }

    /**
     * Provides safe diagnostic information when debugging.
     * This is used by the CredentialsSecurity trait.
     *
     * @return array<string, mixed>
     */
    private function getSafeDebugInfo(): array
    {
        return [
            'driver'   => $this->getDriverName(),
            'host'     => $this->host,
            'port'     => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'options'  => '[hidden]',
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertValidPort(int $port): void
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException("Invalid port number: {$port}");
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertNotEmpty(string $value, string $fieldName): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException("Database credential field '{$fieldName}' cannot be empty.");
        }
    }
}
