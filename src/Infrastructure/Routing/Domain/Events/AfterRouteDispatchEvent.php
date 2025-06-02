<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Domain\Events;

use App\Infrastructure\Routing\Domain\Events\Contracts\RoutingEventInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\ServerRequestInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;

/**
 * AfterRouteDispatchEvent
 *
 * Dispatched after the controller action associated with a route has been executed.
 * Carries the full context, including the original request, route, controller action, 
 * and the response/result produced by the action.
 * Enables logging, metrics, auditing, and post-processing hooks.
 */
final class AfterRouteDispatchEvent implements RoutingEventInterface
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
     * @var ControllerAction
     */
    private ControllerAction $controllerAction;

    /**
     * @var mixed
     */
    private $result;

    /**
     * @var \DateTimeImmutable
     */
    private \DateTimeImmutable $occurredAt;

    /**
     * AfterRouteDispatchEvent constructor.
     *
     * @param ServerRequestInterface $request The original request.
     * @param HttpRouteInterface $route The matched route.
     * @param ControllerAction $controllerAction The controller action that was dispatched.
     * @param mixed $result The response or value returned by the controller action.
     * @param \DateTimeImmutable|null $occurredAt [optional] Event timestamp.
     */
    public function __construct(
        ServerRequestInterface $request,
        HttpRouteInterface $route,
        ControllerAction $controllerAction,
        $result,
        ?\DateTimeImmutable $occurredAt = null
    ) {
        $this->request = $request;
        $this->route = $route;
        $this->controllerAction = $controllerAction;
        $this->result = $result;
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
     * Returns the matched route.
     */
    public function route(): HttpRouteInterface
    {
        return $this->route;
    }

    /**
     * Returns the controller action that was executed.
     */
    public function controllerAction(): ControllerAction
    {
        return $this->controllerAction;
    }

    /**
     * Returns the result or response produced by the controller action.
     */
    public function result()
    {
        return $this->result;
    }

    /**
     * Returns the timestamp of when this event occurred.
     */
    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
