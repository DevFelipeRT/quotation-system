<?php

declare(strict_types=1);

namespace Session\Exceptions;

use RuntimeException;

/**
 * Thrown when a session driver is not supported or not configured.
 *
 * Typically raised during driver resolution when the specified session driver
 * is not recognized or lacks a corresponding implementation.
 */
final class UnsupportedSessionDriverException extends RuntimeException
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * Constructs a new UnsupportedSessionDriverException.
     *
     * @param string               $message  Explanation of the unsupported driver error.
     * @param array<string, mixed> $context  Optional contextual data (e.g. driver name).
     * @param int                  $code     Optional internal error code.
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
     * Returns metadata describing the context of the failure.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
