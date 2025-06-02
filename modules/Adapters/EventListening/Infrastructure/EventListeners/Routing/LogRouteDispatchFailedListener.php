<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\EventListeners\Routing;

use App\Adapters\EventListening\Domain\Support\AbstractEventListener;
use App\Infrastructure\Routing\Domain\Events\RouteDispatchFailedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs when the dispatch of a controller action fails due to an exception.
 */
final class LogRouteDispatchFailedListener extends AbstractEventListener
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    protected function eventType(): string
    {
        return RouteDispatchFailedEvent::class;
    }

    protected function handle(object $event): void
    {
        /** @var RouteDispatchFailedEvent $event */
        $this->logger->error('Route dispatch failed.', [
            'request' => [
                'method' => $event->request()->method()->value(),
                'path' => $event->request()->path()->value(),
            ],
            'controller' => $event->controllerAction()?->controllerClass(),
            'action' => $event->controllerAction()?->method(),
            'exception' => [
                'type' => get_class($event->exception()),
                'message' => $event->exception()->getMessage(),
                'code' => $event->exception()->getCode(),
                'trace' => $event->exception()->getTraceAsString(),
            ],
            'timestamp' => $event->occurredAt()->format(DATE_ATOM),
        ]);
    }
}
