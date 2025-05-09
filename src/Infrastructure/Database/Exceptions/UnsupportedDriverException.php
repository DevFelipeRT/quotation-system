<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Exceptions;

use App\Shared\Exceptions\InfrastructureException;
use Throwable;

/**
 * Exception thrown when a given database driver is not supported by the system.
 *
 * This exception is typically used by connection factories to reject attempts
 * to instantiate unsupported or unknown database implementations.
 */
final class UnsupportedDriverException extends InfrastructureException
{
    /**
     * Initializes an exception for an invalid or unknown database driver.
     *
     * @param string         $driver   The unsupported driver identifier.
     * @param Throwable|null $previous Optional root cause of the error.
     */
    public function __construct(string $driver, ?Throwable $previous = null)
    {
        parent::__construct(
            message: "Unsupported database driver: {$driver}",
            code: 0,
            context: ['driver' => $driver],
            previous: $previous
        );
    }
}
