<?php

namespace App\Shared\Exceptions;

use Throwable;

/**
 * DatabaseConnectionException
 *
 * Represents a failure to establish a PDO connection to the database.
 * 
 * This exception should be thrown when the connection process fails, either due to
 * invalid credentials, unreachable host, misconfiguration, or unexpected PDO errors.
 */
final class DatabaseConnectionException extends ApplicationException
{
    /**
     * Constructs a new DatabaseConnectionException.
     *
     * @param string $message    Human-readable message (default: Portuguese).
     * @param int $code          Optional error code.
     * @param array $context     Optional associative array with additional context (e.g. host, driver, exception).
     * @param Throwable|null $previous Optional chained exception (e.g., PDOException).
     */
    public function __construct(
        string $message = 'Não foi possível conectar ao banco de dados.',
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $context, $previous);
    }
}
