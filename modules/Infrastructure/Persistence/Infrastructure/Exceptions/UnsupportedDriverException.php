<?php

declare(strict_types=1);

namespace Persistence\Infrastructure\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Thrown when a given database driver is not supported by the system.
 *
 * This typically occurs when a connection factory receives an unknown or
 * unregistered driver identifier during initialization.
 */
final class UnsupportedDriverException extends RuntimeException
{
    /**
     * @param string         $driver    The unsupported driver identifier.
     * @param int            $code      Optional machine-readable error code.
     * @param array          $context   Contextual metadata (e.g., SQL, parameters).
     * @param Throwable|null $previous  Optional previous exception for chaining.
     */
    public function __construct(
        string $driver,
        int $code = 0,
        array $context = [],
        ?Throwable $previous = null
    ) {
        $message = "Unsupported database driver: {$driver}";
        $context = array_merge(['Persistence' => 'Connection'], $context);
        parent::__construct($message, $code, $context, $previous);
    }
}
