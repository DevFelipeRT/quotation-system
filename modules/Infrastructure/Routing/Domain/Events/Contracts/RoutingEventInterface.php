<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Domain\Events\Contracts;

/**
 * RoutingEventInterface
 *
 * Marker interface for all routing-related events.
 * All events dispatched within the Routing module must implement this interface.
 * Designed for compatibility with PSR-14 and custom event dispatchers.
 */
interface RoutingEventInterface
{
    /**
     * Returns the timestamp of when the event was created/dispatched.
     *
     * @return \DateTimeImmutable
     */
    public function occurredAt(): \DateTimeImmutable;
}
