<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Connection\Observers;

use App\Infrastructure\Database\Domain\Connection\Events\ConnectionFailedEvent;
use App\Infrastructure\Database\Domain\Connection\Events\ConnectionSucceededEvent;
use App\Infrastructure\Database\Domain\Connection\Observers\ConnectionObserverInterface;
use App\Infrastructure\Logging\Domain\LogEntry;
use App\Infrastructure\Logging\Domain\LogLevelEnum;
use App\Infrastructure\Logging\LoggerInterface;

/**
 * Observes database connection events and logs them to the system logger.
 *
 * This observer is infrastructure-agnostic and records connection events using
 * a standardized channel and structure, enabling consistent diagnostics and monitoring.
 */
final class ConnectionLoggerObserver implements ConnectionObserverInterface
{
    private const CHANNEL = 'infrastructure.database';

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Handles a database connection event and records it to the log system.
     *
     * @param object $event The connection event instance.
     * @return void
     */
    public function handle(object $event): void
    {
        match (true) {
            $event instanceof ConnectionSucceededEvent => $this->logger->log(new LogEntry(
                level: LogLevelEnum::INFO,
                message: $event->message,
                context: [
                    'driver' => $event->driver,
                    ...$event->metadata
                ],
                channel: self::CHANNEL
            )),

            $event instanceof ConnectionFailedEvent => $this->logger->log(new LogEntry(
                level: LogLevelEnum::ERROR,
                message: 'Database connection failed: ' . $event->error,
                context: [
                    'driver' => $event->driver,
                    ...$event->metadata
                ],
                channel: self::CHANNEL
            )),

            default => null
        };
    }
}
