<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Domain\Events;

use App\Infrastructure\Routing\Domain\Events\Contracts\RoutingEventInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\RouteRequestInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;

/**
 * BeforeRouteDispatchEvent
 *
 * Dispatched after a request matches a route, but before the controller action is executed.
 * Allows listeners to implement cross-cutting concerns such as authentication, authorization,
 * logging, contextual modifications, or even short-circuiting the dispatch.
 */
final class BeforeRouteDispatchEvent implements RoutingEventInterface
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
     * @var ControllerAction
     */
    private ControllerAction $controllerAction;

    /**
     * @var \DateTimeImmutable
     */
    private \DateTimeImmutable $occurredAt;

    /**
     * BeforeRouteDispatchEvent constructor.
     *
     * @param RouteRequestInterface $request The incoming HTTP request.
     * @param HttpRouteInterface $route The matched route.
     * @param ControllerAction $controllerAction The controller action about to be dispatched.
     * @param \DateTimeImmutable|null $occurredAt [optional] Event timestamp.
     */
    public function __construct(
        RouteRequestInterface $request,
        HttpRouteInterface $route,
        ControllerAction $controllerAction,
        ?\DateTimeImmutable $occurredAt = null
    ) {
        $this->request = $request;
        $this->route = $route;
        $this->controllerAction = $controllerAction;
        $this->occurredAt = $occurredAt ?? new \DateTimeImmutable();
    }

    /**
     * Returns the incoming request.
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
     * Returns the controller action about to be dispatched.
     */
    public function controllerAction(): ControllerAction
    {
        return $this->controllerAction;
    }

    /**
     * Returns the timestamp of when this event occurred.
     */
    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
