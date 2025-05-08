<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Exceptions;

use Throwable;

/**
 * Represents a failure to establish a connection to the database.
 *
 * This exception is typically thrown when the configured database driver,
 * credentials, or network settings are invalid, or when the underlying
 * PDO driver encounters a connection error.
 */
final class DatabaseConnectionException extends InfrastructureException
{
    /**
     * @param string         $message   A human-readable explanation of the error.
     * @param int            $code      Optional machine-readable error code.
     * @param array          $context   Key-value data for diagnostic or logging purposes.
     * @param Throwable|null $previous  The underlying cause of the failure, if available.
     */
    public function __construct(
        string $message = 'Unable to connect to the database.',
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $context, $previous);
    }
}
