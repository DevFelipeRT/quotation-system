<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Infrastructure\EventListeners\Routing;

use App\Adapters\EventListening\Domain\Support\AbstractEventListener;
use App\Infrastructure\Routing\Domain\Events\AfterRouteDispatchEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * Logs after the controller action for a route has been dispatched.
 */
final class LogAfterRouteDispatchListener extends AbstractEventListener
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    protected function eventType(): string
    {
        return AfterRouteDispatchEvent::class;
    }

    protected function handle(object $event): void
    {
        /** @var AfterRouteDispatchEvent $event */
        $this->logger->info('After dispatching controller action.', [
            'request' => [
                'method' => $event->request()->method()->value(),
                'path' => $event->request()->path()->value(),
            ],
            'controller' => $event->controllerAction()->controllerClass(),
            'action' => $event->controllerAction()->method(),
            'result_type' => is_object($event->result()) ? get_class($event->result()) : gettype($event->result()),
            'timestamp' => $event->occurredAt()->format(DATE_ATOM),
        ]);
    }
}
