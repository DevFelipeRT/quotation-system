<?php

declare(strict_types=1);

namespace Persistence\Domain\Event;

use DateTimeInterface;

/**
 * Event dispatched after a successful SQL execution.
 *
 * Provides details for instrumentation layers (e.g. logging, metrics),
 * without exposing sensitive database internals.
 */
final class RequestExecutedEvent
{
    /**
     * @param string $request      The SQL request executed.
     * @param array  $parameters   The bound parameters used in the execution.
     * @param int    $affectedRows Number of rows affected or returned.
     */
    public function __construct(
        public readonly string $request,
        public readonly array $parameters,
        public readonly int $affectedRows,
        public readonly DateTimeInterface $timestamp
        
    ) {}
}
