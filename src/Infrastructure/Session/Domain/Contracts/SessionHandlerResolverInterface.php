<?php

namespace App\Infrastructure\Session\Domain\Contracts;

/**
 * Defines the contract for resolving a session handler implementation
 * based on configuration or context.
 */
interface SessionHandlerResolverInterface
{
    /**
     * Resolves and returns a session handler instance.
     *
     * @return SessionHandlerInterface
     */
    public function resolve(): SessionHandlerInterface;
}
