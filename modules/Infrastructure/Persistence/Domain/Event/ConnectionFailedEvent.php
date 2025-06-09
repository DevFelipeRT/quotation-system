<?php

declare(strict_types=1);

namespace Persistence\Domain\Event;

use Throwable;

/**
 * Signals that a database connection attempt has failed.
 *
 * This event is dispatched to observers when a connection cannot be established,
 * providing relevant metadata for diagnostics, telemetry, or alerting systems.
 * Sensitive values such as credentials are excluded.
 */
final class ConnectionFailedEvent
{
    /**
     * @param string $driver    The driver used during the failed attempt (e.g., mysql, pgsql).
     * @param Throwable $exception     The throwable returned by the underlying failure.
     * @param array  $metadata      Optional key-value diagnostic context (host, port, database).
     */
    public function __construct(
        public readonly string $driver,
        public readonly Throwable $exception,
        public readonly array $metadata = []
    ) {}
}
