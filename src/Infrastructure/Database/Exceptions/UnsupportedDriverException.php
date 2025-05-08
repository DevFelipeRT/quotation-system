<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Exceptions;

/**
 * Exception thrown when a specified database driver is not supported by the system.
 *
 * Typically used by connection factories to block attempts to instantiate
 * unsupported or misconfigured database strategies.
 */
final class UnsupportedDriverException extends InfrastructureException
{
    /**
     * Constructs an exception for an invalid or unknown database driver.
     *
     * @param string $driver The unsupported driver identifier.
     */
    public function __construct(string $driver)
    {
        parent::__construct(
            message: "Unsupported database driver: {$driver}",
            context: ['driver' => $driver]
        );
    }
}
