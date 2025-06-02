<?php

declare(strict_types=1);

namespace Session\Exceptions;

use RuntimeException;

/**
 * Thrown when a session cannot be properly destroyed.
 *
 * This may occur due to misconfigured session handlers, output already sent,
 * corrupted session state, or unexpected PHP behavior.
 * Allows inclusion of contextual data for observability.
 */
final class SessionDestroyException extends RuntimeException
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * Constructs a new SessionDestroyException.
     *
     * @param string               $message  Explanation of the destruction failure.
     * @param array<string, mixed> $context  Optional diagnostic context (e.g. handler state).
     * @param int                  $code     Optional internal code.
     * @param \Throwable|null      $previous Optional underlying exception.
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
     * Returns structured context describing the failure.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
