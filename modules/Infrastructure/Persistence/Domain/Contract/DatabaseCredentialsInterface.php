<?php

declare(strict_types=1);

namespace Persistence\Domain\Contract;

/**
 * Contract for providing credentials and configuration
 * required to establish a database connection.
 *
 * Implementations of this interface should encapsulate all
 * necessary information to construct a valid DSN, as well as
 * access credentials and additional connection options.
 *
 * This abstraction allows infrastructure-level components
 * to remain agnostic to the specific database engine in use
 * (e.g., MySQL, PostgreSQL, SQLite).
 *
 * Sensitive data such as passwords must be handled securely
 * and never exposed through logging, serialization, or public interfaces.
 */
interface DatabaseCredentialsInterface
{
    /**
     * Returns the fully formatted Data Source Name (DSN)
     * compatible with the corresponding PDO driver.
     *
     * @return string The PDO-compliant DSN string.
     */
    public function getDsn(): string;

    /**
     * Returns the database username, if applicable.
     *
     * @return string|null The username, or null if not required.
     */
    public function getUsername(): ?string;

    /**
     * Returns the database password, if applicable.
     *
     * @return string|null The password, or null if not required.
     */
    public function getPassword(): ?string;

    /**
     * Returns an associative array of PDO driver options.
     *
     * These options may include persistent connections,
     * timeout settings, error modes, and more.
     *
     * @return array<string, mixed> An array of PDO options.
     */
    public function getOptions(): array;

    /**
     * Returns the name of the driver (e.g., "mysql", "pgsql", "sqlite").
     *
     * This is useful for conditional behavior based on the driver type.
     *
     * @return string
     */
    public function getDriverName(): string;
}
