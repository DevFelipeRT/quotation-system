<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Execution;

use App\Infrastructure\Database\Exceptions\QueryExecutionException;
use App\Infrastructure\Database\Events\QueryExecutedEvent;
use App\Infrastructure\Database\Events\QueryFailedEvent;
use App\Infrastructure\Database\Observers\RequestObserverInterface;
use PDO;
use PDOException;

/**
 * Executes SQL queries via PDO and emits query lifecycle events for instrumentation.
 *
 * This class is responsible for the actual execution of parameterized SQL statements,
 * but delegates monitoring, logging, and auditing to external observers via events.
 * 
 * It implements the DatabaseRequestInterface contract and serves as a foundational
 * component for repository and service layers that require consistent and traceable
 * access to relational data sources.
 */
final class PdoDatabaseRequest implements DatabaseRequestInterface
{
    /**
     * @param PDO $pdo Active PDO connection.
     * @param RequestObserverInterface[] $observers Observer objects to be notified of query outcomes.
     */
    public function __construct(
        private readonly PDO $pdo,
        private readonly array $observers = []
    ) {}

    /**
     * Executes a SELECT query and returns the resulting rows.
     *
     * @param string $sql    A SQL SELECT statement with placeholders.
     * @param array  $params Values to bind to the statement parameters.
     * 
     * @return array A list of associative arrays representing result rows.
     *
     * @throws QueryExecutionException On execution failure.
     */
    public function select(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $this->notify(new QueryExecutedEvent(
                query: $sql,
                parameters: $params,
                affectedRows: $stmt->rowCount()
            ));

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->notify(new QueryFailedEvent(
                query: $sql,
                parameters: $params,
                errorMessage: $e->getMessage()
            ));

            throw new QueryExecutionException(
                message: 'Failed to execute SELECT query.',
                context: ['sql' => $sql, 'params' => $params],
                previous: $e
            );
        }
    }

    /**
     * Executes a DML statement (INSERT, UPDATE, DELETE) and returns affected row count.
     *
     * @param string $sql    A SQL statement with placeholders.
     * @param array  $params Values to bind.
     *
     * @return int Number of rows affected by the execution.
     *
     * @throws QueryExecutionException On execution failure.
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $this->notify(new QueryExecutedEvent(
                query: $sql,
                parameters: $params,
                affectedRows: $stmt->rowCount()
            ));

            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->notify(new QueryFailedEvent(
                query: $sql,
                parameters: $params,
                errorMessage: $e->getMessage()
            ));

            throw new QueryExecutionException(
                message: 'Failed to execute DML query.',
                context: ['sql' => $sql, 'params' => $params],
                previous: $e
            );
        }
    }

    /**
     * Checks if a SELECT query returns any rows.
     *
     * @param string $sql
     * @param array $params
     * @return bool True if query returns at least one row.
     */
    public function exists(string $sql, array $params = []): bool
    {
        return !empty($this->select($sql, $params));
    }

    /**
     * Begins a transactional context.
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commits the current transaction.
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Rolls back the active transaction.
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Retrieves the last inserted ID from the connection.
     *
     * @return int
     */
    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Notifies all observers of a query execution event.
     *
     * @param object $event An event instance (QueryExecutedEvent or QueryFailedEvent).
     */
    private function notify(object $event): void
    {
        foreach ($this->observers as $observer) {
            $observer->handle($event);
        }
    }
}
