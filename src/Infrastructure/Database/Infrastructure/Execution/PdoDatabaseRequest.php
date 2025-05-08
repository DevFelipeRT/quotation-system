<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Execution;

use PDO;
use PDOException;

/**
 * Executes parameterized SQL statements via PDO and emits query lifecycle events.
 *
 * This class is responsible for secure and observable interaction with relational databases,
 * emitting domain events for external instrumentation (e.g., logging, metrics).
 */
final class PdoDatabaseRequest implements DatabaseRequestInterface
{
    /**
     * @param PDO $pdo An active and trusted PDO connection.
     * @param RequestObserverInterface[] $observers External listeners for query lifecycle events.
     *
     * @throws \InvalidArgumentException If any provided observer is invalid.
     */
    public function __construct(
        private readonly PDO $pdo,
        private readonly array $observers = []
    ) {
        foreach ($this->observers as $observer) {
            if (!$observer instanceof RequestObserverInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected instance of RequestObserverInterface, got: %s',
                    is_object($observer) ? get_class($observer) : gettype($observer)
                ));
            }
        }
    }

    /**
     * Executes a SELECT query and returns the result set.
     *
     * @param string $sql A parameterized SQL SELECT statement.
     * @param array $params Key-value pairs to bind to placeholders.
     * @return array List of result rows.
     *
     * @throws QueryExecutionException On execution failure.
     */
    public function select(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->prepareAndExecute($sql, $params);
            $this->notify(new QueryExecutedEvent($sql, $params, $stmt->rowCount()));

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->notify(new QueryFailedEvent($sql, $params, $e->getMessage()));
            throw new QueryExecutionException(
                'Failed to execute SELECT query.',
                ['sql' => $sql, 'params' => $params],
                $e
            );
        }
    }

    /**
     * Executes a DML query (INSERT, UPDATE, DELETE).
     *
     * @param string $sql A parameterized SQL statement.
     * @param array $params Bindings for placeholders.
     * @return int Number of rows affected.
     *
     * @throws QueryExecutionException On execution failure.
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->prepareAndExecute($sql, $params);
            $affected = $stmt->rowCount();

            $this->notify(new QueryExecutedEvent($sql, $params, $affected));
            return $affected;
        } catch (PDOException $e) {
            $this->notify(new QueryFailedEvent($sql, $params, $e->getMessage()));
            throw new QueryExecutionException(
                'Failed to execute DML query.',
                ['sql' => $sql, 'params' => $params],
                $e
            );
        }
    }

    /**
     * Returns true if the query returns at least one result row.
     *
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function exists(string $sql, array $params = []): bool
    {
        return !empty($this->select($sql, $params));
    }

    /**
     * Begins a transaction.
     *
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commits the current transaction.
     *
     * @return void
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Rolls back the current transaction.
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Returns the last inserted ID from the current connection.
     *
     * @return int
     */
    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Notifies all registered observers of a query lifecycle event.
     *
     * @param QueryExecutedEvent|QueryFailedEvent $event
     * @return void
     */
    private function notify(QueryExecutedEvent|QueryFailedEvent $event): void
    {
        foreach ($this->observers as $observer) {
            $observer->handle($event);
        }
    }

    /**
     * Prepares and executes a PDO statement with given parameters.
     *
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     *
     * @throws PDOException If preparation or execution fails.
     */
    private function prepareAndExecute(string $sql, array $params): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
