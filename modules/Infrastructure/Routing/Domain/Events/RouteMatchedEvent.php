<?php

declare(strict_types=1);

namespace Routing\Domain\Events;

use Routing\Domain\Events\Contracts\RoutingEventInterface;
use Routing\Presentation\Http\Contracts\ServerRequestInterface;
use Routing\Presentation\Http\Contracts\HttpRouteInterface;

/**
 * RouteMatchedEvent
 *
 * Dispatched when an incoming request is successfully matched to a route.
 * Carries full context for listeners, such as audit logging, monitoring, authorization, etc.
 */
final class RouteMatchedEvent implements RoutingEventInterface
{
    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;

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
     * @param ServerRequestInterface $request The matched request.
     * @param HttpRouteInterface $route The route matched to this request.
     * @param \DateTimeImmutable|null $occurredAt Event timestamp (optional, defaults to now).
     */
    public function __construct(
        ServerRequestInterface $request,
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
    public function request(): ServerRequestInterface
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
