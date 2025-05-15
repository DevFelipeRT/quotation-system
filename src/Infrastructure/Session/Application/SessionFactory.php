<?php

namespace App\Infrastructure\Session\Application;

use App\Infrastructure\Session\Domain\Contracts\SessionHandlerInterface;
use App\Infrastructure\Session\Domain\Contracts\SessionHandlerResolverInterface;

/**
 * Responsible for building and returning the active SessionHandlerInterface instance.
 *
 * Uses a resolver to determine the appropriate implementation.
 */
final class SessionFactory
{
    /**
     * @param SessionHandlerResolverInterface $resolver
     */
    public function __construct(
        private readonly SessionHandlerResolverInterface $resolver
    ) {}

    /**
     * Returns a fully configured session handler.
     *
     * @return SessionHandlerInterface
     */
    public function create(): SessionHandlerInterface
    {
        return $this->resolver->resolve();
    }
}
