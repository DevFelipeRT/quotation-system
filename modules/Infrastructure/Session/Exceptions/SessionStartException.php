<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Exceptions;

use RuntimeException;

/**
 * Thrown when a session cannot be started properly.
 *
 * This may occur due to output already sent, configuration errors,
 * invalid session save paths, or unexpected runtime conditions.
 */
final class SessionStartException extends RuntimeException
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * Constructs a new SessionStartException.
     *
     * @param string               $message  Explanation of the start failure.
     * @param array<string, mixed> $context  Optional diagnostic metadata (e.g. headers, status).
     * @param int                  $code     Optional internal error code.
     * @param \Throwable|null      $previous Optional chained exception.
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
     * Returns contextual data about the failure.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
