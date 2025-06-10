<?php

declare(strict_types=1);

namespace Persistence\Infrastructure\Exceptions;

use Persistence\Infrastructure\Exceptions\Contract\PersistenceException;
use Throwable;

/**
 * Represents an error that occurred during SQL request execution.
 *
 * Includes contextual metadata (e.g., request string, bindings) to aid debugging
 * and supports exception chaining.
 */
final class RequestExecutionException extends PersistenceException
{
    /**
     * @param string         $message   A descriptive error message.
     * @param int            $code      Optional machine-readable error code.
     * @param array          $context   Contextual metadata (e.g., SQL, parameters).
     * @param Throwable|null $previous  Optional previous exception for chaining.
     */
    public function __construct(
        string $message = 'Failed to execute database request.',
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        $context = array_merge(['Persistence' => 'Execution'], $context);
        parent::__construct($message, $code, $context, $previous);
    }
}
