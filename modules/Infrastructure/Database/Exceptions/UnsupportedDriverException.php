<?php

declare(strict_types=1);

namespace Database\Exceptions;

use App\Shared\Exceptions\InfrastructureException;
use Throwable;

/**
 * Thrown when a given database driver is not supported by the system.
 *
 * This typically occurs when a connection factory receives an unknown or
 * unregistered driver identifier during initialization.
 */
final class UnsupportedDriverException extends InfrastructureException
{
    /**
     * Initializes the exception for an unsupported or unknown database driver.
     *
     * @param string              $driver    The unsupported driver identifier.
     * @param Throwable|null      $previous  The underlying cause of the failure, if any.
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
