<?php

declare(strict_types=1);

namespace Persistence\Domain\Contract;

/**
 * DatabaseExecutionInterface defines the contract for services responsible
 * for executing parametrized SQL queries against a relational database.
 *
 * It provides unified methods for executing arbitrary queries and managing
 * transactions, independent of underlying drivers or database engines.
 *
 * Implementations must guarantee safe execution, proper parameter binding,
 * and consistent exception handling.
 *
 * Example usage:
 *   $result = $service->execute($query); // $query implements QueryInterface
 *
 * @author
 */
interface DatabaseExecutionInterface
{
    /**
     * Executes the given query and returns the result.
     *
     * For SELECT: Returns an array of records.
     * For INSERT: Returns the last insert ID.
     * For UPDATE/DELETE: Returns the number of affected rows.
     *
     * @param QueryInterface $query
     * @return mixed
     * @throws PersistenceException On execution failure.
     */
    public function execute(QueryInterface $query): mixed;

    /**
     * Returns the last inserted auto-increment ID (if supported).
     *
     * @return int|string
     */
    public function lastInsertId(): int|string;

    /**
     * Returns the number of rows affected by the last executed statement.
     *
     * @return int
     */
    public function affectedRows(): int;

    /**
     * Begins a database transaction.
     *
     * @throws PersistenceException On failure to start the transaction.
     */
    public function beginTransaction(): void;

    /**
     * Commits the current transaction.
     *
     * @throws PersistenceException On failure to commit the transaction.
     */
    public function commit(): void;

    /**
     * Rolls back the current transaction.
     *
     * @throws PersistenceException On failure to roll back the transaction.
     */
    public function rollback(): void;
}
