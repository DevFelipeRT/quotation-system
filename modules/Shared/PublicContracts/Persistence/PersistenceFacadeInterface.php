<?php

declare(strict_types=1);

namespace PublicContracts\Persistence;

use Persistence\Infrastructure\Contract\QueryBuilderInterface;
use Persistence\Domain\Contract\QueryInterface;

/**
 * PersistenceFacadeInterface defines a high-level contract for all persistence operations,
 * unifying query building and execution through a single access point.
 *
 * Example:
 *   $query = $facade->queryBuilder()
 *             ->table('users')
 *             ->where('email', '=', 'user@domain.com')
 *             ->build();
 *   $result = $facade->execute($query);
 *
 * @author
 */
interface PersistenceFacadeInterface
{
    /**
     * Returns a new QueryBuilderInterface instance for query construction.
     *
     * @return QueryBuilderInterface
     */
    public function queryBuilder(): QueryBuilderInterface;

    /**
     * Executes the given query and returns the result.
     *
     * @param QueryInterface $query
     * @return mixed
     * @throws PersistenceException
     */
    public function execute(QueryInterface $query): mixed;

    /**
     * Begins a transaction.
     *
     * @throws PersistenceException
     */
    public function beginTransaction(): void;

    /**
     * Commits the current transaction.
     *
     * @throws PersistenceException
     */
    public function commit(): void;

    /**
     * Rolls back the current transaction.
     *
     * @throws PersistenceException
     */
    public function rollback(): void;

    /**
     * Returns the last inserted auto-increment ID.
     *
     * @return int|string
     */
    public function lastInsertId(): int|string;

    /**
     * Returns the number of affected rows from the last statement.
     *
     * @return int
     */
    public function affectedRows(): int;
}
