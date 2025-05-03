<?php

namespace App\Shared\Exceptions;

use Exception;
use Throwable;

/**
 * Base exception for all system-level exceptions.
 */
abstract class ApplicationException extends Exception
{
    protected array $context;

    /**
     * ApplicationException constructor.
     *
     * @param string $message   The exception message.
     * @param int $code         The exception code.
     * @param array $context    Additional context to help with debugging.
     * @param Throwable|null $previous Optional previous exception for chaining.
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Returns additional context provided during exception construction.
     *
     * @return array
     */
    public function context(): array
    {
        return $this->context;
    }
}
