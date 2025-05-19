<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\EventListeners\Routing;

use App\Adapters\EventListening\Domain\Support\AbstractEventListener;
use App\Infrastructure\Routing\Domain\Events\RouteMatchedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs when a route is successfully matched to a request.
 */
final class LogRouteMatchedListener extends AbstractEventListener
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    protected function eventType(): string
    {
        return RouteMatchedEvent::class;
    }

    protected function handle(object $event): void
    {
        /** @var RouteMatchedEvent $event */
        $this->logger->info('Route matched.', [
            'request' => [
                'method' => $event->request()->method()->value(),
                'path' => $event->request()->path()->value(),
            ],
            'route' => [
                'name' => $event->route()->name(),
                'controller' => $event->route()->controllerAction()->controllerClass(),
                'action' => $event->route()->controllerAction()->method(),
            ],
            'timestamp' => $event->occurredAt()->format(DATE_ATOM),
        ]);
    }
}
