<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Domain\Events;

use App\Infrastructure\Routing\Domain\Events\Contracts\RoutingEventInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\RouteRequestInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;

/**
 * RouteMatchedEvent
 *
 * Dispatched when an incoming request is successfully matched to a route.
 * Carries full context for listeners, such as audit logging, monitoring, authorization, etc.
 */
final class RouteMatchedEvent implements RoutingEventInterface
{
    /**
     * @var RouteRequestInterface
     */
    private RouteRequestInterface $request;

    /**
     * @var HttpRouteInterface
     */
    private HttpRouteInterface $route;

    /**
     * @var \DateTimeImmutable
     */
    private \DateTimeImmutable $occurredAt;

    /**
     * RouteMatchedEvent constructor.
     *
     * @param RouteRequestInterface $request The matched request.
     * @param HttpRouteInterface $route The route matched to this request.
     * @param \DateTimeImmutable|null $occurredAt Event timestamp (optional, defaults to now).
     */
    public function __construct(
        RouteRequestInterface $request,
        HttpRouteInterface $route,
        ?\DateTimeImmutable $occurredAt = null
    ) {
        $this->request = $request;
        $this->route = $route;
        $this->occurredAt = $occurredAt ?? new \DateTimeImmutable();
    }

    /**
     * Returns the matched request.
     */
    public function request(): RouteRequestInterface
    {
        return $this->request;
    }

    /**
     * Returns the matched route.
     */
    public function route(): HttpRouteInterface
    {
        return $this->route;
    }

    /**
     * Returns when this event occurred.
     */
    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
