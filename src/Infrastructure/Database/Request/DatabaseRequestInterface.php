<?php

namespace App\Infrastructure\Database\Request;

/**
 * Defines a contract for executing database operations.
 *
 * Implementations must manage query execution and transaction
 * control over a connection (e.g., PDO), without exposing internals.
 *
 * This interface is intended for repository and service layer use.
 *
 * @package App\Infrastructure\Database\Request
 */
interface DatabaseRequestInterface
{
    /**
     * Executes a SELECT query and returns the result set.
     *
     * @param string $sql A SQL SELECT statement.
     * @param array $params Parameters to bind to the query.
     * @return array List of results as associative arrays.
     */
    public function select(string $sql, array $params = []): array;

    /**
     * Executes an INSERT, UPDATE, or DELETE statement.
     *
     * @param string $sql A SQL statement.
     * @param array $params Parameters to bind.
     * @return int Number of affected rows.
     */
    public function execute(string $sql, array $params = []): int;

    /**
     * Determines whether a given query returns any results.
     *
     * @param string $sql A SQL SELECT statement.
     * @param array $params Parameters to bind.
     * @return bool True if at least one result is found.
     */
    public function exists(string $sql, array $params = []): bool;

    /**
     * Begins a database transaction.
     */
    public function beginTransaction(): void;

    /**
     * Commits the active transaction.
     */
    public function commit(): void;

    /**
     * Rolls back the active transaction.
     */
    public function rollback(): void;

    /**
     * Returns the last inserted auto-increment ID.
     *
     * @return int
     */
    public function lastInsertId(): int;
}
