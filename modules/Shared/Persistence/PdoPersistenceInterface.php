<?php

namespace App\Persistence; // Example namespace

use PDO;
use PDOException;
use PDOStatement;

/**
 * Interface PdoPersistenceInterface
 * Defines a contract for PDO-based persistence operations,
 * intended for use with the Facade pattern.
 */
interface PdoPersistenceInterface
{
    /**
     * Inserts a new row into a table.
     *
     * @param string $table The name of the table.
     * @param array $data An associative array of columns and values to be inserted.
     * @return string|false The ID of the last inserted row on success, or false on failure.
     * @throws PDOException On query execution error.
     */
    public function insert(string $table, array $data): string|false;

    /**
     * Selects a single row from a table or query.
     *
     * @param string $sql The SQL query to be executed.
     * @param array $params Parameters for the prepared statement.
     * @param int $fetchMode The PDO fetch mode (e.g., PDO::FETCH_ASSOC, PDO::FETCH_OBJ).
     * @param mixed $fetchArgument Additional argument for the fetch mode (e.g., class name for PDO::FETCH_CLASS).
     * @param array $ctorArgs Constructor arguments for PDO::FETCH_CLASS.
     * @return mixed The resulting row in the specified format, or false if no row is found.
     * @throws PDOException On query execution error.
     */
    public function selectOne(string $sql, array $params = [], int $fetchMode = PDO::FETCH_ASSOC, mixed $fetchArgument = null, array $ctorArgs = []): mixed;

    /**
     * Selects multiple rows from a table or query.
     *
     * @param string $sql The SQL query to be executed.
     * @param array $params Parameters for the prepared statement.
     * @param int $fetchMode The PDO fetch mode.
     * @param mixed $fetchArgument Additional argument for the fetch mode.
     * @param array $ctorArgs Constructor arguments for PDO::FETCH_CLASS.
     * @return array An array of rows in the specified format. Returns an empty array if no rows are found.
     * @throws PDOException On query execution error.
     */
    public function selectAll(string $sql, array $params = [], int $fetchMode = PDO::FETCH_ASSOC, mixed $fetchArgument = null, array $ctorArgs = []): array;

    /**
     * Updates rows in a table.
     *
     * @param string $table The name of the table.
     * @param array $data An associative array of columns and values to be updated.
     * @param array $conditions An associative array of conditions for the WHERE clause.
     * @return int The number of affected rows.
     * @throws PDOException On query execution error.
     */
    public function update(string $table, array $data, array $conditions): int;

    /**
     * Deletes rows from a table.
     *
     * @param string $table The name of the table.
     * @param array $conditions An associative array of conditions for the WHERE clause.
     * @return int The number of affected rows.
     * @throws PDOException On query execution error.
     */
    public function delete(string $table, array $conditions): int;

    /**
     * Executes an SQL query that does not return a result set (e.g., INSERT, UPDATE, DELETE).
     *
     * @param string $sql The SQL query to be executed.
     * @param array $params Parameters for the prepared statement.
     * @return int The number of affected rows.
     * @throws PDOException On query execution error.
     */
    public function execute(string $sql, array $params = []): int;

    /**
     * Prepares and executes an SQL query, returning the PDOStatement object.
     * Useful for SELECT queries where the caller may want to iterate over the results
     * or use specific PDOStatement functionalities.
     *
     * @param string $sql The SQL query to be executed.
     * @param array $params Parameters for the prepared statement.
     * @return PDOStatement The resulting PDOStatement object after execution.
     * @throws PDOException On statement preparation or execution error.
     */
    public function query(string $sql, array $params = []): PDOStatement;

    /**
     * Fetches a single scalar value from the first column of the first row of a query.
     *
     * @param string $sql The SQL query to be executed.
     * @param array $params Parameters for the prepared statement.
     * @param int $columnIndex The 0-based index of the column to fetch.
     * @return mixed The scalar value, or false if no row/column is found.
     * @throws PDOException On query execution error.
     */
    public function fetchScalar(string $sql, array $params = [], int $columnIndex = 0): mixed;

    /**
     * Begins a transaction.
     *
     * @return bool True on success, false on failure.
     * @throws PDOException If there is already an active transaction or the driver does not support transactions.
     */
    public function beginTransaction(): bool;

    /**
     * Commits the current transaction.
     *
     * @return bool True on success, false on failure.
     * @throws PDOException If there is no active transaction.
     */
    public function commit(): bool;

    /**
     * Rolls back the current transaction.
     *
     * @return bool True on success, false on failure.
     * @throws PDOException If there is no active transaction.
     */
    public function rollBack(): bool;

    /**
     * Checks if a transaction is currently active.
     *
     * @return bool True if a transaction is active, false otherwise.
     */
    public function inTransaction(): bool;

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param string|null $name Name of the sequence object from which the ID should be returned (driver-dependent).
     * @return string|false The ID of the last inserted row as a string, or false on failure.
     */
    public function getLastInsertId(?string $name = null): string|false;

    /**
     * Returns the underlying PDO instance.
     * Useful for advanced or driver-specific operations not covered by the interface.
     *
     * @return PDO The PDO instance.
     */
    public function getPDO(): PDO;
}