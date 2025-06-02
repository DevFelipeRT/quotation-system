<?php

declare(strict_types=1);

namespace Session\Exceptions;

use DomainException;

/**
 * Thrown when session data reconstruction fails due to invalid or missing values.
 *
 * This exception supports structured context information to assist with debugging
 * and observability, such as the offending field or value.
 */
final class InvalidSessionDataException extends DomainException
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * Constructs a new InvalidSessionDataException.
     *
     * @param string               $message  Descriptive error message.
     * @param array<string, mixed> $context  Optional structured context (e.g. invalid keys).
     * @param int                  $code     Optional error code.
     * @param \Throwable|null      $previous Optional previous exception for chaining.
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
     * Returns structured context for the failure, if provided.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
