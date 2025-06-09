<?php

declare(strict_types=1);

namespace Persistence\Infrastructure;

use Persistence\Domain\Contract\DatabaseExecutionInterface;
use Persistence\Domain\Event\RequestExecutedEvent;
use Persistence\Domain\Event\RequestFailedEvent;
use Shared\Event\EventRecording;
use PDO;
use PDOException;
use PDOStatement;
use Persistence\Infrastructure\Exceptions\RequestExecutionException;

/**
 * PDO-backed implementation of DatabaseExecutionInterface.
 *
 * Encapsulates all SQL execution logic and records domain events
 * for success or failure without dispatching them immediately.
 *
 * Events recorded (use releaseEvents()):
 * - RequestExecutedEvent
 * - RequestFailedEvent
 *
 * @final
 */
final class PdoExecutionService implements DatabaseExecutionInterface
{
    use EventRecording;

    private ?PDOStatement $lastStatement = null;

    public function __construct(
        private readonly PDO $pdo
    ) {}

    public function select(string $sql, array $params = []): array
    {
        return $this->executeRequest($sql, $params, fn(PDOStatement $stmt) => $stmt->fetchAll());
    }

    public function execute(string $sql, array $params = []): int
    {
        return $this->executeRequest($sql, $params, fn(PDOStatement $stmt) => $stmt->rowCount());
    }

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

    public function affectedRows(): int
    {
        return $this->lastStatement?->rowCount() ?? 0;
    }

    /**
     * Prepares and executes the sql request, recording events based on outcome.
     *
     * @template T
     * @param string $sql
     * @param array $params
     * @param callable(PDOStatement): T $onSuccess
     * @return T
     * @throws RequestExecutionException
     */
    private function executeRequest(string $sql, array $params, callable $onSuccess)
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $this->lastStatement = $stmt;

            $this->recordEvent(new RequestExecutedEvent(
                request: $sql,
                parameters: $params,
                affectedRows: $this->affectedRows(),
                timestamp: new \DateTimeImmutable()
            ));

            return $onSuccess($stmt);
        } catch (PDOException $e) {
            $this->recordEvent(new RequestFailedEvent(
                request: $sql,
                parameters: $params,
                exception: $e,
                timestamp: new \DateTimeImmutable()
            ));

            throw new RequestExecutionException(
                'Failed to execute database query.',
                0,
                ['sql' => $sql, 'params' => $params],
                $e
            );
        }
    }
}
