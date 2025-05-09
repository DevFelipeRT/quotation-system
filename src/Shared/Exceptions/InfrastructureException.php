<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base exception for infrastructure-level failures such as I/O, database,
 * network, or service connectivity errors.
 *
 * Supports contextual data for structured logging and diagnostics.
 */
class InfrastructureException extends RuntimeException
{
    /**
     * @var array<string, mixed>
     */
    protected array $context;

    /**
     * @param string            $message   Human-readable explanation of the failure.
     * @param int               $code         Optional machine-readable error code.
     * @param array             $context    Structured context for logs or monitoring.
     * @param Throwable|null    $previous Underlying cause, if any.
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Returns contextual data relevant to the exception.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
