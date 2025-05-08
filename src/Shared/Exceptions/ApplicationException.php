<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use Exception;
use Throwable;

/**
 * Base exception for all high-level application exceptions.
 *
 * Supports additional debugging context and exception chaining.
 */
abstract class ApplicationException extends Exception
{
    protected array $context;

    /**
     * @param string          $message   Exception message.
     * @param int             $code      Error code (optional).
     * @param array           $context   Additional context for debugging/logging.
     * @param Throwable|null  $previous  Chained exception, if any.
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
     * Returns additional context provided during exception construction.
     *
     * @return array
     */
    public function context(): array
    {
        return $this->context;
    }
}
