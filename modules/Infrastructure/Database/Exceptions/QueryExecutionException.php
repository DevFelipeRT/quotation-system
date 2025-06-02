<?php

declare(strict_types=1);

namespace Database\Exceptions;

use App\Shared\Exceptions\InfrastructureException;
use Throwable;

/**
 * Represents an error that occurred during SQL query execution.
 *
 * Includes contextual metadata (e.g., query string, bindings) to aid debugging
 * and supports exception chaining.
 */
final class QueryExecutionException extends InfrastructureException
{
    /**
     * @param string         $message   A descriptive error message.
     * @param int            $code      Optional machine-readable error code.
     * @param array          $context   Contextual metadata (e.g., SQL, parameters).
     * @param Throwable|null $previous  Optional previous exception for chaining.
     */
    public function __construct(
        string $message = 'Failed to execute database query.',
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $context, $previous);
    }
}
