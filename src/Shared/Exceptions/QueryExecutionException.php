<?php

namespace App\Shared\Exceptions;

use Throwable;

/**
 * QueryExecutionException
 *
 * Represents a failure during the execution of a SQL query via PDO.
 *
 * This exception is used in the infrastructure layer to signal that
 * a query could not be prepared or executed, and wraps the original PDOException.
 */
final class QueryExecutionException extends ApplicationException
{
    /**
     * Constructs a new QueryExecutionException.
     *
     * @param string $message  Human-readable message (default: Portuguese).
     * @param int $code        Optional error code.
     * @param array $context   Optional contextual metadata (e.g. SQL, parameters).
     * @param Throwable|null $previous Optional previous exception (usually PDOException).
     */
    public function __construct(
        string $message = 'Erro ao executar a consulta no banco de dados.',
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $context, $previous);
    }
}
