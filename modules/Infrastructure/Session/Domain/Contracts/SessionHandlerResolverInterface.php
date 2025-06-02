<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Domain\Contracts;

/**
 * Defines the contract for resolving the class name of a session handler implementation
 * based on the configured driver or runtime context.
 *
 * This interface does not define instantiation logic.
 * The resolved class must implement SessionHandlerInterface.
 */
interface SessionHandlerResolverInterface
{
    /**
     * Resolves and returns the fully qualified class name
     * of the session handler implementation to be used.
     *
     * @return class-string<SessionHandlerInterface>
     */
    public function resolve(): string;
}
