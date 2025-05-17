<?php

declare(strict_types=1);

namespace App\Kernel\Adapters\Providers;

use App\Adapters\EventListening\Infrastructure\EventListeners\Database\LogConnectionFailureListener;
use App\Adapters\EventListening\Infrastructure\EventListeners\Database\LogQueryExecutedListener;
use App\Adapters\EventListening\Infrastructure\EventListeners\Database\LogQueryFailureListener;
use App\Adapters\EventListening\Infrastructure\EventListeners\Database\LogSuccessfulConnectionListener;
use App\Infrastructure\Database\Domain\Connection\Events\ConnectionFailedEvent;
use App\Infrastructure\Database\Domain\Connection\Events\ConnectionSucceededEvent;
use App\Infrastructure\Database\Domain\Execution\Events\QueryExecutedEvent;
use App\Infrastructure\Database\Domain\Execution\Events\QueryFailedEvent;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;
use App\Kernel\Adapters\Contracts\EventBindingProviderInterface;

/**
 * Provides bindings between database-related events and logging listeners.
 */
final class DatabaseEventBindingProvider implements EventBindingProviderInterface
{
    public function __construct(
        private readonly PsrLoggerInterface $logger
    ) {}

    /**
     * @return array<class-string, EventListenerInterface[]>
     */
    public function bindings(): array
    {
        return [
            ConnectionFailedEvent::class => [
                new LogConnectionFailureListener($this->logger),
            ],
            ConnectionSucceededEvent::class => [
                new LogSuccessfulConnectionListener($this->logger),
            ],
            QueryFailedEvent::class => [
                new LogQueryFailureListener($this->logger),
            ],
            QueryExecutedEvent::class => [
                new LogQueryExecutedListener($this->logger),
            ],
        ];
    }
}
