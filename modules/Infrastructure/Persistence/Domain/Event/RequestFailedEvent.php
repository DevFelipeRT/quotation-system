<?php

declare(strict_types=1);

namespace Persistence\Domain\Event;

use DateTimeInterface;
use Throwable;

/**
 * Event dispatched when a SQL request execution fails.
 *
 * Provides structured context for logging, debugging or alerting subsystems.
 * Designed to avoid direct exposure of raw exception objects or internal stack traces.
 */
final class RequestFailedEvent
{
    /**
     * @param string $request       The SQL request that failed.
     * @param array  $parameters    Parameters passed to the failed request.
     * @param string $errorMessage  A safe, high-level error description.
     */
    public function __construct(
        public readonly string $request,
        public readonly array $parameters,
        public readonly Throwable $exception,
        public readonly DateTimeInterface $timestamp
    ) {}
}
