<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Represents an error that occurred during SQL query execution.
 *
 * This exception includes contextual metadata to aid debugging
 * and supports exception chaining via previous exception.
 */
final class QueryExecutionException extends RuntimeException
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * @param string $message A descriptive error message.
     * @param array $context Contextual metadata (e.g., SQL and parameters).
     * @param Throwable|null $previous Optional previous exception for chaining.
     */
    public function __construct(
        string $message,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->context = $context;
    }

    /**
     * Returns contextual information about the query failure.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
