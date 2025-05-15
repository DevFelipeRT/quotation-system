<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Exceptions;

use DomainException;

/**
 * Thrown when a SessionContext cannot be constructed due to invalid input.
 *
 * This exception is typically raised when the provided locale is malformed
 * or fails validation. It supports contextual metadata to aid in debugging.
 */
final class InvalidSessionContextException extends DomainException
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * Constructs a new InvalidSessionContextException.
     *
     * @param string               $message  Explanation of the failure.
     * @param array<string, mixed> $context  Optional contextual data (e.g. field => value).
     * @param int                  $code     Optional error code.
     * @param \Throwable|null      $previous Optional cause.
     */
    public function __construct(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Returns contextual metadata associated with the failure.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
