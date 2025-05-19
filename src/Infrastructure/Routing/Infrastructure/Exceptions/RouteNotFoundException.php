<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Exceptions;

use RuntimeException;

/**
 * Thrown when the routing infrastructure is unable to resolve a request to any registered route.
 *
 * This exception signals a 404 scenario at the routing level and allows inclusion of
 * contextual diagnostic data for observability, logging, and advanced error handling.
 */
final class RouteNotFoundException extends RuntimeException
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * Constructs a new RouteNotFoundException.
     *
     * @param string               $message  Explanation of the route resolution failure.
     * @param array<string, mixed> $context  Optional diagnostic context (e.g. request data, matching state).
     * @param int                  $code     Optional internal code.
     * @param \Throwable|null      $previous Optional underlying exception.
     */
    public function __construct(
        string $message = 'No route matched the given request.',
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Returns structured context describing the route resolution failure.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
