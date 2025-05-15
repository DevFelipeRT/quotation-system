<?php

namespace App\Infrastructure\Session\Application;

use App\Infrastructure\Session\Domain\Contracts\SessionHandlerInterface;
use App\Infrastructure\Session\Domain\Contracts\SessionHandlerResolverInterface;
use App\Infrastructure\Session\Exceptions\UnsupportedSessionDriverException;
use App\Infrastructure\Session\Infrastructure\Drivers\NativeSessionHandler;
use App\Infrastructure\Session\Infrastructure\Support\SessionConfig;

/**
 * Resolves the appropriate session handler implementation based on configuration.
 */
final class DefaultSessionHandlerResolver implements SessionHandlerResolverInterface
{
    /**
     * Returns a resolved SessionHandlerInterface implementation.
     *
     * @throws UnsupportedSessionDriverException if the configured driver is not supported.
     *
     * @return SessionHandlerInterface
     */
    public function resolve(): SessionHandlerInterface
    {
        $driver = SessionConfig::defaultDriver();

        return match ($driver) {
            'native' => new NativeSessionHandler(),
            default => throw new UnsupportedSessionDriverException("Unsupported session driver: {$driver}"),
        };
    }
}
