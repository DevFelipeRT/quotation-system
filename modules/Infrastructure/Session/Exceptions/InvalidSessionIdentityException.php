<?php

declare(strict_types=1);

namespace Session\Exceptions;

use DomainException;

/**
 * Thrown when a UserIdentity object cannot be constructed due to invalid data.
 *
 * This may include invalid user ID (non-positive integer), empty names or roles,
 * or improperly typed values. Supports contextual metadata for diagnostics.
 */
final class InvalidSessionIdentityException extends DomainException
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * Constructs a new InvalidSessionIdentityException.
     *
     * @param string               $message  Explanation of the failure.
     * @param array<string, mixed> $context  Optional field-specific context.
     * @param int                  $code     Optional error code.
     * @param \Throwable|null      $previous Optional previous exception.
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
     * Returns structured context data associated with the error.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
