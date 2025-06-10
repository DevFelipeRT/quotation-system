<?php

declare(strict_types=1);

namespace Persistence\Infrastructure\Exceptions;

use Persistence\Infrastructure\Exceptions\Contract\PersistenceException;
use Throwable;

/**
 * Thrown when the system fails to establish a database connection.
 *
 * Common causes include invalid credentials, unavailable network,
 * unsupported drivers, or internal driver errors (e.g., PDO failures).
 *
 * This exception includes optional contextual metadata and supports exception chaining.
 */
final class DatabaseConnectionException extends PersistenceException
{
    public array $context = ['Persistence' => 'Connection'];

    /**
     * Initializes the exception for a database connection failure.
     *
     * @param string         $message   A human-readable explanation of the error.
     * @param int            $code      Optional machine-readable error code.
     * @param array<string, mixed> $context   Key-value data for diagnostic or logging purposes.
     * @param Throwable|null $previous  The underlying cause of the failure, if available.
     */
    public function __construct(
        string $message = 'Unable to connect to the database.',
        int $code = 0,
        ?array $context = [],
        ?Throwable $previous = null
    ) {
        $context = array_merge(['Persistence' => 'Connection'], $context);
        parent::__construct($message, $code, $context, $previous);
    }
}
