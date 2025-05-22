<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Exceptions;

use RuntimeException;

/**
 * Thrown when a route was found for the request path, but the HTTP method is not allowed.
 *
 * This exception signals a 405 Method Not Allowed scenario at the routing level,
 * providing optional context for observability and structured error handling.
 */
final class MethodNotAllowedException extends RuntimeException
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * Constructs a new MethodNotAllowedException.
     *
     * @param string               $message  Explanation of the method restriction.
     * @param array<string, mixed> $context  Optional diagnostic context (e.g. allowed methods, request info).
     * @param int                  $code     Optional internal error code.
     * @param \Throwable|null      $previous Optional prior exception.
     */
    public function __construct(
        string $message = 'HTTP method not allowed for the requested route.',
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Returns structured context describing the method restriction.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
