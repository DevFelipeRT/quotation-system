<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Domain\Events;

use App\Infrastructure\Routing\Domain\Events\Contracts\RoutingEventInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\RouteRequestInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use \DateTimeImmutable;

/**
 * RouteResolvedEvent
 *
 * Dispatched when a route has been resolved for a given request.
 * This can occur after advanced matching, URL rewriting, or alias resolution,
 * and allows listeners to hook into the routing process at a more granular stage.
 */
final class RouteResolvedEvent implements RoutingEventInterface
{
    /**
     * @var RouteRequestInterface
     */
    private RouteRequestInterface $request;

    /**
     * @var HttpRouteInterface
     */
    private HttpRouteInterface $resolvedRoute;

    /**
     * @var string|null
     */
    private ?string $resolutionType;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $occurredAt;

    /**
     * RouteResolvedEvent constructor.
     *
     * @param RouteRequestInterface $request The original request.
     * @param HttpRouteInterface $resolvedRoute The final resolved route.
     * @param string|null $resolutionType [optional] Type or reason for resolution (e.g., 'rewrite', 'alias', 'redirect').
     * @param DateTimeImmutable|null $occurredAt [optional] Event timestamp.
     */
    public function __construct(
        RouteRequestInterface $request,
        HttpRouteInterface $resolvedRoute,
        ?string $resolutionType = null,
        ?DateTimeImmutable $occurredAt = null
    ) {
        $this->request = $request;
        $this->resolvedRoute = $resolvedRoute;
        $this->resolutionType = $resolutionType;
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    /**
     * Returns the original request.
     */
    public function request(): RouteRequestInterface
    {
        return $this->request;
    }

    /**
     * Returns the resolved route.
     */
    public function resolvedRoute(): HttpRouteInterface
    {
        return $this->resolvedRoute;
    }

    /**
     * Returns the resolution type or context (e.g., 'rewrite', 'alias', 'redirect'), if provided.
     */
    public function resolutionType(): ?string
    {
        return $this->resolutionType;
    }

    /**
     * Returns the timestamp of when this event occurred.
     */
    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
