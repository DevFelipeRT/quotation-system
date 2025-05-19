<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\Providers;

use App\Infrastructure\Routing\Domain\Events\RouteMatchedEvent;
use App\Infrastructure\Routing\Domain\Events\RouteNotFoundEvent;
use App\Infrastructure\Routing\Domain\Events\BeforeRouteDispatchEvent;
use App\Infrastructure\Routing\Domain\Events\AfterRouteDispatchEvent;
use App\Infrastructure\Routing\Domain\Events\RouteDispatchFailedEvent;
use App\Infrastructure\Routing\Domain\Events\RouteResolvedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;
use App\Kernel\Adapters\Contracts\EventBindingProviderInterface;

/**
 * Provides bindings between routing-related events and logging listeners.
 */
final class RoutingEventBindingProvider implements EventBindingProviderInterface
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    /**
     * Returns the routing event-to-listener bindings.
     *
     * @return array<class-string, array<object>>
     */
    public function bindings(): array
    {
        return [
            RouteMatchedEvent::class => [
                new \App\Adapters\EventListening\Infrastructure\EventListeners\Routing\LogRouteMatchedListener($this->logger),
            ],
            RouteNotFoundEvent::class => [
                new \App\Adapters\EventListening\Infrastructure\EventListeners\Routing\LogRouteNotFoundListener($this->logger),
            ],
            BeforeRouteDispatchEvent::class => [
                new \App\Adapters\EventListening\Infrastructure\EventListeners\Routing\LogBeforeRouteDispatchListener($this->logger),
            ],
            AfterRouteDispatchEvent::class => [
                new \App\Adapters\EventListening\Infrastructure\EventListeners\Routing\LogAfterRouteDispatchListener($this->logger),
            ],
            RouteDispatchFailedEvent::class => [
                new \App\Adapters\EventListening\Infrastructure\EventListeners\Routing\LogRouteDispatchFailedListener($this->logger),
            ],
            RouteResolvedEvent::class => [
                new \App\Adapters\EventListening\Infrastructure\EventListeners\Routing\LogRouteResolvedListener($this->logger),
            ],
        ];
    }
}
