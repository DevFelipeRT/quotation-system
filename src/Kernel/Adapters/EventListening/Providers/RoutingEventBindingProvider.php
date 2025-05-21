<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\EventListening\Providers;

use App\Kernel\Adapters\EventListening\Contracts\EventBindingProviderInterface;
use App\Adapters\EventListening\Infrastructure\EventListeners\Routing\{
    LogAfterRouteDispatchListener,
    LogBeforeRouteDispatchListener,
    LogRouteDispatchFailedListener,
    LogRouteMatchedListener,
    LogRouteNotFoundListener,
    LogRouteResolvedListener
};
use App\Infrastructure\Routing\Domain\Events\{
    RouteMatchedEvent,
    RouteNotFoundEvent,
    BeforeRouteDispatchEvent,
    AfterRouteDispatchEvent,
    RouteDispatchFailedEvent,
    RouteResolvedEvent
};

/**
 * RoutingEventBindingProvider
 *
 * Declares bindings between routing-related events and their listener classes.
 * Does not instantiate listeners or require dependencies; listeners are resolved dynamically.
 */
final class RoutingEventBindingProvider implements EventBindingProviderInterface
{
    /**
     * Returns event-to-listener class bindings for routing domain events.
     *
     * @return array<class-string, class-string[]>
     */
    public function bindings(): array
    {
        return [
            RouteMatchedEvent::class => [
                LogRouteMatchedListener::class,
            ],
            RouteNotFoundEvent::class => [
                LogRouteNotFoundListener::class,
            ],
            BeforeRouteDispatchEvent::class => [
                LogBeforeRouteDispatchListener::class,
            ],
            AfterRouteDispatchEvent::class => [
                LogAfterRouteDispatchListener::class,
            ],
            RouteDispatchFailedEvent::class => [
                LogRouteDispatchFailedListener::class,
            ],
            RouteResolvedEvent::class => [
                LogRouteResolvedListener::class,
            ],
        ];
    }
}
