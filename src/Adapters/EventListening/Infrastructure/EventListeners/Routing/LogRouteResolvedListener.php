<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\EventListeners\Routing;

use App\Adapters\EventListening\Domain\Support\AbstractEventListener;
use App\Infrastructure\Routing\Domain\Events\RouteResolvedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs when a route is resolved for a request.
 */
final class LogRouteResolvedListener extends AbstractEventListener
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    protected function eventType(): string
    {
        return RouteResolvedEvent::class;
    }

    protected function handle(object $event): void
    {
        /** @var RouteResolvedEvent $event */
        $this->logger->info('Route resolved.', [
            'request' => [
                'method' => $event->request()->method()->value(),
                'path' => $event->request()->path()->value(),
            ],
            'route' => [
                'name' => $event->resolvedRoute()->name(),
                'controller' => $event->resolvedRoute()->controllerAction()->controllerClass(),
                'action' => $event->resolvedRoute()->controllerAction()->method(),
            ],
            'resolution_type' => $event->resolutionType(),
            'timestamp' => $event->occurredAt()->format(DATE_ATOM),
        ]);
    }
}
