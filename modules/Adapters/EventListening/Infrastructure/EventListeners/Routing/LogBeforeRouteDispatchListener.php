<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\EventListeners\Routing;

use App\Adapters\EventListening\Domain\Support\AbstractEventListener;
use App\Infrastructure\Routing\Domain\Events\BeforeRouteDispatchEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs before dispatching the controller action for a route.
 */
final class LogBeforeRouteDispatchListener extends AbstractEventListener
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    protected function eventType(): string
    {
        return BeforeRouteDispatchEvent::class;
    }

    protected function handle(object $event): void
    {
        /** @var BeforeRouteDispatchEvent $event */
        $this->logger->info('Before dispatching controller action.', [
            'request' => [
                'method' => $event->request()->method()->value(),
                'path' => $event->request()->path()->value(),
            ],
            'controller' => $event->controllerAction()->controllerClass(),
            'action' => $event->controllerAction()->method(),
            'timestamp' => $event->occurredAt()->format(DATE_ATOM),
        ]);
    }
}
