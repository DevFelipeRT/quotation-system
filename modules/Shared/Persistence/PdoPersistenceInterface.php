<?php

declare(strict_types=1);

namespace Persistence\Application\Facade;

use PDO;
use Persistence\Infrastructure\Exceptions\DatabaseConnectionException;
use Persistence\Infrastructure\Exceptions\RequestExecutionException;

/**
 * PersistenceFacadeInterface serves as the unified contract
 * to encapsulate and abstract database connection and execution services.
 *
 * Provides simplified access to database operations, ensuring clients
 * interact with a cohesive, streamlined API.
 */
interface PersistenceFacadeInterface
{
    /**
     * Establishes a database connection and returns a PDO instance.
     *
     * @return PDO
     *
     * @throws DatabaseConnectionException if connection fails.
     */
    public function connect(): PDO;

    /**
     * Executes a SELECT query and returns all results as an array.
     *
     * @param string $sql SQL query with placeholders.
     * @param array<string, mixed> $params Query parameters.
     *
     * @return array Result set.
     *
     * @throws RequestExecutionException if query execution fails.
     */
    public function select(string $sql, array $params = []): array;

    /**
     * Executes an INSERT, UPDATE, or DELETE query.
     *
     * @param string $sql SQL command with placeholders.
     * @param array<string, mixed> $params Query parameters.
     *
     * @return int Number of rows affected.
     *
     * @throws RequestExecutionException if query execution fails.
     */
    public function execute(string $sql, array $params = []): int;

    /**
     * Checks whether any record exists for a given query.
     *
     * @param string $sql SQL query with placeholders.
     * @param array<string, mixed> $params Query parameters.
     *
     * @return bool True if at least one record exists, otherwise false.
     *
     * @throws RequestExecutionException if query execution fails.
     */
    public function exists(string $sql, array $params = []): bool;

    /**
     * Initiates a database transaction.
     *
     * @throws RequestExecutionException if the transaction fails to start.
     */
    public function beginTransaction(): void;

    /**
     * Commits the current database transaction.
     *
     * @throws RequestExecutionException if commit fails.
     */
    public function commit(): void;

    /**
     * Rolls back the current database transaction.
     *
     * @throws RequestExecutionException if rollback fails.
     */
    public function rollback(): void;

    /**
     * Retrieves the ID of the last inserted record.
     *
     * @return int Last inserted ID.
     *
     * @throws RequestExecutionException if operation fails.
     */
    public function lastInsertId(): int;

    /**
     * Retrieves the number of rows affected by the last query.
     *
     * @return int Number of rows affected.
     */
    public function affectedRows(): int;

    /**
     * Indicates whether there is an active PDO connection.
     *
     * @return bool True if connected, false otherwise.
     */
    public function isConnected(): bool;

    /**
     * Retrieves the database driver name (e.g., 'mysql', 'pgsql', 'sqlite').
     *
     * @return string Database driver name.
     */
    public function getDriver(): string;
}
