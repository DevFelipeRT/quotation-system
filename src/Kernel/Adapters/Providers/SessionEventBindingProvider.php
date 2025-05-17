<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\Providers;

use App\Adapters\EventListening\Infrastructure\EventListeners\Session\LogSessionStartedListener;
use App\Adapters\EventListening\Infrastructure\EventListeners\Session\LogSessionDataChangedListener;
use App\Adapters\EventListening\Infrastructure\EventListeners\Session\LogSessionDestroyedListener;
use App\Infrastructure\Session\Domain\Events\SessionStartedEvent;
use App\Infrastructure\Session\Domain\Events\SessionDataChangedEvent;
use App\Infrastructure\Session\Domain\Events\SessionDestroyedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;
use App\Kernel\Adapters\Contracts\EventBindingProviderInterface;

/**
 * Provides bindings between session-related events and logging listeners.
 */
final class SessionEventBindingProvider implements EventBindingProviderInterface
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    /**
     * Returns the session event-to-listener bindings.
     *
     * @return array<class-string, array<object>>
     */
    public function bindings(): array
    {
        return [
            SessionStartedEvent::class => [
                new LogSessionStartedListener($this->logger),
            ],
            SessionDataChangedEvent::class => [
                new LogSessionDataChangedListener($this->logger),
            ],
            SessionDestroyedEvent::class => [
                new LogSessionDestroyedListener($this->logger),
            ],
        ];
    }
}
