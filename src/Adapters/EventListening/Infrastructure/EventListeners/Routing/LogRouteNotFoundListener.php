<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\EventListeners\Routing;

use App\Adapters\EventListening\Domain\Support\AbstractEventListener;
use App\Infrastructure\Routing\Domain\Events\RouteNotFoundEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs when a route is not found for a request.
 */
final class LogRouteNotFoundListener extends AbstractEventListener
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    protected function eventType(): string
    {
        return RouteNotFoundEvent::class;
    }

    protected function handle(object $event): void
    {
        /** @var RouteNotFoundEvent $event */
        $this->logger->warning('Route not found.', [
            'request' => [
                'method' => $event->request()->method()->value(),
                'path' => $event->request()->path()->value(),
            ],
            'message' => $event->message(),
            'timestamp' => $event->occurredAt()->format(DATE_ATOM),
        ]);
    }
}
