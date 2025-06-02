<?php

declare(strict_types=1);

namespace Database\Infrastructure\Execution;

use Database\Domain\Execution\DatabaseRequestInterface;
use Database\Domain\Execution\Events\QueryExecutedEvent;
use Database\Domain\Execution\Events\QueryFailedEvent;
use Database\Exceptions\QueryExecutionException;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use PDO;
use PDOException;
use PDOStatement;

/**
 * PDO-backed implementation of the DatabaseRequestInterface.
 *
 * Encapsulates all SQL execution logic and emits domain events through
 * a dispatcher to decouple side-effect processing (e.g., logging, metrics).
 *
 * Events:
 * - QueryExecutedEvent: dispatched on successful execution.
 * - QueryFailedEvent: dispatched on failure with context.
 *
 * @package Database\Infrastructure\Execution
 */
final class PdoDatabaseRequest implements DatabaseRequestInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    /**
     * Executes a SELECT query and returns the result set.
     *
     * @param string $sql
     * @param array $params
     * @return array
     * @throws QueryExecutionException
     */
    public function select(string $sql, array $params = []): array
    {
        return $this->executeQuery($sql, $params, fn(PDOStatement $stmt) => $stmt->fetchAll());
    }

    /**
     * Executes a DML query and returns the number of affected rows.
     *
     * @param string $sql
     * @param array $params
     * @return int
     * @throws QueryExecutionException
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->executeQuery($sql, $params, fn(PDOStatement $stmt) => $stmt->rowCount());
    }

    /**
     * Verifies if any rows exist for the provided query.
     *
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function exists(string $sql, array $params = []): bool
    {
        return !empty($this->select($sql, $params));
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Prepares and executes the given query, handles success/failure via events.
     *
     * @template T
     * @param string $sql
     * @param array $params
     * @param callable(PDOStatement): T $onSuccess
     * @return T
     * @throws QueryExecutionException
     */
    private function executeQuery(string $sql, array $params, callable $onSuccess)
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $result = $onSuccess($stmt);
            $this->dispatcher->dispatch(new QueryExecutedEvent($sql, $params, $stmt->rowCount()));
            return $result;
        } catch (PDOException $e) {
            $this->dispatcher->dispatch(new QueryFailedEvent($sql, $params, $e->getMessage()));
            throw new QueryExecutionException(
                'Failed to execute database query.',
                0,
                ['sql' => $sql, 'params' => $params],
                $e
            );
        }
    }
}
