<?php

declare(strict_types=1);

namespace Persistence\Infrastructure\Exceptions\Contract;

use RuntimeException;
use Throwable;

/**
 * Abstract base exception for all persistence-layer failures.
 *
 * This exception supports contextual metadata for diagnostics and exception chaining.
 * All specific persistence exceptions should extend this class.
 *
 * Example usage in a child exception:
 *   throw new DatabaseConnectionException('Failed...', 0, ['host' => 'db.example.com'], $previous);
 *
 * @author
 */
abstract class PersistenceException extends RuntimeException
{
    /**
     * Contextual metadata for diagnostics or logging.
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * Initializes the persistence exception.
     *
     * @param string               $message  Human-readable explanation of the error.
     * @param int                  $code     Optional machine-readable error code.
     * @param array<string, mixed> $context  Key-value metadata for logging or debugging.
     * @param Throwable|null       $previous The underlying cause, if available.
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?array $context = [],
        ?Throwable $previous = null
    ) {
        $this->context = $context ?? [];
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns contextual metadata for this exception.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
