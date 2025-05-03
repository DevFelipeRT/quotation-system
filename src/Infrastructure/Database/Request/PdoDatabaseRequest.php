<?php

namespace App\Infrastructure\Database\Request;

use App\Infrastructure\Logging\LogEntry;
use App\Infrastructure\Logging\LogLevelEnum;
use App\Infrastructure\Logging\LogSanitizer;
use App\Interfaces\Infrastructure\LoggerInterface;
use App\Shared\Exceptions\QueryExecutionException;
use PDO;
use PDOException;

/**
 * PdoDatabaseRequest
 *
 * PDO-based implementation of DatabaseRequestInterface.
 *
 * Responsible for executing SQL queries, handling errors, logging structured events,
 * and abstracting PDO-level operations from higher layers of the system.
 *
 * @package App\Infrastructure\Database\Request
 */
class PdoDatabaseRequest implements DatabaseRequestInterface
{
    private const LOG_CHANNEL = 'database.query';

    private PDO $pdo;
    private LoggerInterface $logger;

    public function __construct(PDO $pdo, LoggerInterface $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    public function select(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $this->log(LogLevelEnum::DEBUG, 'Consulta SELECT executada com sucesso.', $sql, $params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleError('Erro ao executar consulta SELECT.', $sql, $params, $e);
        }
    }

    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $this->log(LogLevelEnum::DEBUG, 'Operação de escrita executada com sucesso.', $sql, $params, $stmt->rowCount());
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->handleError('Erro ao executar operação de escrita.', $sql, $params, $e);
        }
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

    /**
     * Logs a successful database operation.
     *
     * @param LogLevelEnum $level Logging severity.
     * @param string $message Log message in Portuguese.
     * @param string $sql SQL query executed.
     * @param array $params Bound parameters.
     * @param int|null $rowCount Optional number of affected rows.
     */
    private function log(LogLevelEnum $level, string $message, string $sql, array $params = [], ?int $rowCount = null): void
    {
        $context = [
            'sql'    => $sql,
            'params' => LogSanitizer::sanitizeSqlParams($params),
        ];

        if ($rowCount !== null) {
            $context['affected_rows'] = $rowCount;
        }

        $this->logger->log(new LogEntry(
            level: $level,
            message: $message,
            context: $context,
            channel: self::LOG_CHANNEL
        ));
    }

    /**
     * Logs and throws a structured query exception with chained PDOException.
     *
     * @param string $message Description of the failure (Portuguese).
     * @param string $sql SQL that caused the error.
     * @param array $params Parameters passed to the query.
     * @param PDOException $e Original exception.
     *
     * @throws QueryExecutionException
     */
    private function handleError(string $message, string $sql, array $params, PDOException $e): never
    {
        $this->logger->log(new LogEntry(
            level: LogLevelEnum::ERROR,
            message: $message,
            context: [
                'sql'    => $sql,
                'params' => LogSanitizer::sanitizeSqlParams($params),
                'erro'   => $e->getMessage(),
            ],
            channel: self::LOG_CHANNEL
        ));

        throw new QueryExecutionException(
            $message,
            0,
            [
                'sql' => $sql,
                'params' => $params,
            ],
            $e
        );
    }
}
