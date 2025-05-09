<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Execution\Observers;

use App\Infrastructure\Database\Domain\Execution\Events\QueryExecutedEvent;
use App\Infrastructure\Database\Domain\Execution\Events\QueryFailedEvent;
use App\Infrastructure\Database\Domain\Execution\Observers\RequestObserverInterface;
use App\Infrastructure\Logging\Domain\LogEntry;
use App\Infrastructure\Logging\Domain\LogLevelEnum;
use App\Infrastructure\Logging\LoggerInterface;

/**
 * Observes SQL query events and logs them using the system logger.
 *
 * Reacts to both successful and failed executions by producing
 * structured log entries for debugging and monitoring purposes.
 */
final class QueryLoggerObserver implements RequestObserverInterface
{
    private const CHANNEL = 'database.query';

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Handles and logs query execution events.
     *
     * @param object $event Either QueryExecutedEvent or QueryFailedEvent.
     * @return void
     */
    public function handle(object $event): void
    {
        match (true) {
            $event instanceof QueryExecutedEvent => $this->logger->log(new LogEntry(
                level: LogLevelEnum::DEBUG,
                message: 'SQL query executed successfully.',
                context: [
                    'query' => $event->query,
                    'parameters' => $event->parameters,
                    'rows' => $event->affectedRows,
                ],
                channel: self::CHANNEL
            )),

            $event instanceof QueryFailedEvent => $this->logger->log(new LogEntry(
                level: LogLevelEnum::ERROR,
                message: 'SQL query execution failed: ' . $event->errorMessage,
                context: [
                    'query' => $event->query,
                    'parameters' => $event->parameters,
                ],
                channel: self::CHANNEL
            )),

            default => null
        };
    }
}
