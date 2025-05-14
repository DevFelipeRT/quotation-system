<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Exceptions;

use App\Shared\Exceptions\InfrastructureException;
use Throwable;

/**
 * Thrown when the DB_DRIVER environment variable is missing or empty.
 *
 * This error typically indicates a misconfigured environment during bootstrap,
 * where the required database driver value was not supplied or is inaccessible.
 */
final class MissingDriverConfigurationException extends InfrastructureException
{
    /**
     * Initializes the exception for missing database driver configuration.
     *
     * @param string              $message   A human-readable explanation of the error.
     * @param int                 $code      Optional machine-readable error code.
     * @param array<string,mixed> $context   Key-value data for diagnostic or logging purposes.
     * @param Throwable|null      $previous  The underlying cause of the failure, if available.
     */
    public function __construct(
        string $message = 'Missing DB_DRIVER configuration.',
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $context, $previous);
    }
}
