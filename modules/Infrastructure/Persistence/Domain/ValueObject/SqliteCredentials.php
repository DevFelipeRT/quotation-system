<?php

declare(strict_types=1);

namespace Persistence\Domain\ValueObject;

use InvalidArgumentException;
use Persistence\Domain\Contract\DatabaseCredentialsInterface;
use Persistence\Domain\Support\CredentialsSecurity;

/**
 * Immutable Value Object representing SQLite connection credentials.
 *
 * This class encapsulates the location of the SQLite database file
 * or the special ":memory:" identifier for in-memory databases.
 *
 * While SQLite typically does not require authentication, an optional
 * DatabaseSecret can be provided for encryption-enabled builds.
 *
 * @immutable
 */
final class SqliteCredentials implements DatabaseCredentialsInterface
{
    use CredentialsSecurity;

    public function __construct(
        private readonly string $filePath,
        private readonly ?DatabaseSecret $password = null,
        private readonly array $options = []
    ) {
        $this->assertValidPath($filePath);
    }

    public function getDsn(): string
    {
        return $this->isMemoryDatabase()
            ? 'sqlite::memory:'
            : "sqlite:{$this->filePath}";
    }

    public function getUsername(): ?string
    {
        return null; // Not applicable to SQLite
    }

    public function getPassword(): ?string
    {
        return $this->password?->reveal();
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getDriverName(): string
    {
        return 'sqlite';
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
            'filePath' => $this->filePath,
            'options'  => '[hidden]',
        ];
    }

    private function isMemoryDatabase(): bool
    {
        return trim($this->filePath) === ':memory:';
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertValidPath(string $path): void
    {
        if (trim($path) === '') {
            throw new InvalidArgumentException('SQLite file path cannot be empty.');
        }

        if (!str_starts_with($path, ':memory:') && preg_match('/[\r\n]/', $path)) {
            throw new InvalidArgumentException('SQLite file path contains invalid characters.');
        }
    }
}
