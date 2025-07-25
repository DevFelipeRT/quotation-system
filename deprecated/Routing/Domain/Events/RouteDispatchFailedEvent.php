<?php

declare(strict_types=1);

namespace Routing\Domain\Events;

use Routing\Domain\Events\Contracts\RoutingEventInterface;
use Routing\Presentation\Http\Contracts\ServerRequestInterface;
use Routing\Presentation\Http\Contracts\HttpRouteInterface;
use Routing\Domain\ValueObjects\ControllerAction;
use Throwable;

/**
 * RouteDispatchFailedEvent
 *
 * Dispatched when an exception or error occurs during the dispatch
 * of the controller action associated with a route.
 * Carries the request, route, controller action, the thrown exception,
 * and the timestamp for structured error handling and monitoring.
 */
final class RouteDispatchFailedEvent implements RoutingEventInterface
{
    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;

    /**
     * @var HttpRouteInterface|null
     */
    private ?HttpRouteInterface $route;

    /**
     * @var ControllerAction|null
     */
    private ?ControllerAction $controllerAction;

    /**
     * @var Throwable
     */
    private Throwable $exception;

    /**
     * @var \DateTimeImmutable
     */
    private \DateTimeImmutable $occurredAt;

    /**
     * RouteDispatchFailedEvent constructor.
     *
     * @param ServerRequestInterface $request The original request.
     * @param Throwable $exception The exception or error thrown.
     * @param HttpRouteInterface|null $route The matched route (if available).
     * @param ControllerAction|null $controllerAction The controller action (if available).
     * @param \DateTimeImmutable|null $occurredAt [optional] Event timestamp.
     */
    public function __construct(
        ServerRequestInterface $request,
        Throwable $exception,
        ?HttpRouteInterface $route = null,
        ?ControllerAction $controllerAction = null,
        ?\DateTimeImmutable $occurredAt = null
    ) {
        $this->request = $request;
        $this->route = $route;
        $this->controllerAction = $controllerAction;
        $this->exception = $exception;
        $this->occurredAt = $occurredAt ?? new \DateTimeImmutable();
    }

    /**
     * Returns the original request.
     */
    public function request(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Returns the matched route, if available.
     */
    public function route(): ?HttpRouteInterface
    {
        return $this->route;
    }

    /**
     * Returns the controller action, if available.
     */
    public function controllerAction(): ?ControllerAction
    {
        return $this->controllerAction;
    }

    /**
     * Returns the exception or error that was thrown.
     */
    public function exception(): Throwable
    {
        return $this->exception;
    }

    /**
     * Returns the timestamp of when this event occurred.
     */
    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
