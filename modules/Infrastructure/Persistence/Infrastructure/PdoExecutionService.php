<?php

declare(strict_types=1);

namespace Persistence\Infrastructure;

use PDO;
use PDOException;
use Persistence\Infrastructure\Contract\DatabaseExecutionInterface;
use Persistence\Domain\Contract\QueryInterface;
use Persistence\Infrastructure\Exceptions\RequestExecutionException;

/**
 * PdoExecutionService provides a secure, unified interface for executing
 * parametrized SQL queries, as represented by the QueryInterface Value Object.
 *
 * This service supports all major SQL operations (SELECT, INSERT, UPDATE, DELETE),
 * manages parameter binding, and centralizes error handling and (optional) event recording.
 *
 * Usage:
 *   $query = (new QueryInterfaceBuilder)->table('users')->where('status', '=', 'active')->build();
 *   $result = $pdoExecutionService->execute($query); // For SELECT, returns array
 *
 * @author
 */
final class PdoExecutionService implements DatabaseExecutionInterface
{
    /**
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Optionally holds the last executed PDOStatement.
     *
     * @var \PDOStatement|null
     */
    private ?\PDOStatement $lastStatement = null;

    /**
     * Constructor.
     *
     * @param PDO $pdo PDO connection instance, fully configured.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Executes a QueryInterface Value Object and returns the result.
     *
     * For SELECT: Returns all rows as an array.
     * For INSERT: Returns the last inserted ID (int).
     * For UPDATE/DELETE: Returns the affected rows (int).
     *
     * @param QueryInterface $query
     * @return mixed
     * @throws RequestExecutionException
     */
    public function execute(QueryInterface $query): mixed
    {
        $sql = $query->getSql();
        $bindings = $query->getBindings();

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($bindings as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            $stmt->execute();
            $this->lastStatement = $stmt;

            // Dispatch by operation type
            $operation = $this->detectOperation($sql);

            return match ($operation) {
                'select' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'insert' => $this->pdo->lastInsertId(),
                'update', 'delete' => $stmt->rowCount(),
                default => true, // For operations that only need to indicate success
            };
        } catch (PDOException $e) {
            throw new RequestExecutionException(
                'Failed to execute database query.',
                0,
                ['sql' => $sql, 'params' => $bindings],
                $e
            );
        }
    }

    /**
     * Returns the last inserted auto-increment ID.
     *
     * @return int|string
     */
    public function lastInsertId(): int|string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Returns the number of rows affected by the last operation.
     *
     * @return int
     */
    public function affectedRows(): int
    {
        return $this->lastStatement?->rowCount() ?? 0;
    }

    // ---------------------- Transaction Control ------------------------

    /**
     * Begins a transaction.
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
     * Rolls back the current transaction.
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    // ---------------------- Internal Utility ------------------------

    /**
     * Detects the operation type from the SQL statement.
     * Used to determine what should be returned by execute().
     *
     * @param string $sql
     * @return string 'select' | 'insert' | 'update' | 'delete'
     */
    private function detectOperation(string $sql): string
    {
        $type = strtolower(strtok(ltrim($sql), " \t\n\r")); // First word
        return match ($type) {
            'select' => 'select',
            'insert' => 'insert',
            'update' => 'update',
            'delete' => 'delete',
            default   => $type,
        };
    }
}
