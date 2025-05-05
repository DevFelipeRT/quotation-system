<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Exceptions;

use Throwable;
use App\Exceptions\Infrastructure\InfrastructureException;

/**
 * Represents a failure during the execution of a SQL statement.
 *
 * This exception is typically thrown when a PDO operation fails due to:
 * - invalid SQL syntax,
 * - parameter binding errors,
 * - driver-specific restrictions,
 * - or database-level constraints.
 */
final class QueryExecutionException extends InfrastructureException
{
    /**
     * @param string         $message   Optional error message for logs or user feedback.
     * @param int            $code      Optional numeric error code.
     * @param array          $context   Key-value diagnostic data (SQL, parameters, bindings).
     * @param Throwable|null $previous  The original exception (e.g., PDOException).
     */
    public function __construct(
        string $message = 'Database query execution failed.',
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $context, $previous);
    }
}
