<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Domain\Connection\Events;

/**
 * Signals that a database connection was successfully established.
 *
 * This event may be used by logging, telemetry, or audit subsystems to track
 * connection initialization. It contains only non-sensível metadata to preserve
 * security in public logs or monitoring layers.
 */
final class ConnectionSucceededEvent
{
    /**
     * @param string $driver  The driver used for the connection (e.g., mysql, pgsql, sqlite).
     * @param string $message Optional description or context for the event.
     * @param array  $metadata Optional key-value pairs for monitoring or metrics.
     */
    public function __construct(
        public readonly string $driver,
        public readonly string $message = 'Database connection established successfully.',
        public readonly array $metadata = []
    ) {}
}
