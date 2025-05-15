<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\Providers;

use App\Kernel\Adapters\Contracts\EventBindingProviderInterface;

/**
 * Binds session-related events to their listeners.
 */
final class SessionEventBindingProvider implements EventBindingProviderInterface
{
    public function __construct(
        private readonly LoggableSessionEventListener $listener
    ) {}

    public function register(EventBindingResolverInterface $resolver): void
    {
        $resolver->bind(SessionStartedEvent::class, [$this->listener, 'onSessionStarted']);
        $resolver->bind(SessionDataChangedEvent::class, [$this->listener, 'onSessionDataChanged']);
        $resolver->bind(SessionDestroyedEvent::class, [$this->listener, 'onSessionDestroyed']);
    }
}
