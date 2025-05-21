<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\EventListening\Providers;

use App\Kernel\Adapters\EventListening\Contracts\EventBindingProviderInterface;
use App\Adapters\EventListening\Infrastructure\EventListeners\Database\{
    LogConnectionFailureListener,
    LogQueryExecutedListener,
    LogQueryFailureListener,
    LogSuccessfulConnectionListener
};
use App\Infrastructure\Database\Domain\Connection\Events\{
    ConnectionFailedEvent,
    ConnectionSucceededEvent
};
use App\Infrastructure\Database\Domain\Execution\Events\{
    QueryExecutedEvent,
    QueryFailedEvent
};

/**
 * DatabaseEventBindingProvider
 *
 * Declares bindings between database-related events and their corresponding listener classes.
 * This provider does not instantiate listeners or require dependencies; all listeners are resolved dynamically.
 */
final class DatabaseEventBindingProvider implements EventBindingProviderInterface
{
    /**
     * Returns event-to-listener class bindings for database domain events.
     *
     * @return array<class-string, class-string[]>
     */
    public function bindings(): array
    {
        return [
            ConnectionFailedEvent::class => [
                LogConnectionFailureListener::class,
            ],
            ConnectionSucceededEvent::class => [
                LogSuccessfulConnectionListener::class,
            ],
            QueryFailedEvent::class => [
                LogQueryFailureListener::class,
            ],
            QueryExecutedEvent::class => [
                LogQueryExecutedListener::class,
            ],
        ];
    }
}
