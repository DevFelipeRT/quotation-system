<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Application;

use App\Infrastructure\Session\Domain\Contracts\SessionHandlerInterface;
use App\Infrastructure\Session\Domain\Contracts\SessionHandlerResolverInterface;

/**
 * Factory responsible for instantiating the session handler implementation
 * resolved via the SessionHandlerResolverInterface.
 *
 * This factory centralizes handler construction, decoupled from event infrastructure.
 */
final class SessionFactory
{
    public function __construct(
        private readonly SessionHandlerResolverInterface $resolver
    ) {}

    /**
     * Instantiates and returns the resolved session handler.
     *
     * @return SessionHandlerInterface
     */
    public function create(): SessionHandlerInterface
    {
        $handlerClass = $this->resolver->resolve();

        /** @var SessionHandlerInterface $handler */
        $handler = new $handlerClass();

        return $handler;
    }
}
