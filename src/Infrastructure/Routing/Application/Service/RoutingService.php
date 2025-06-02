<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Application\Service;

use App\Infrastructure\Routing\Application\Contracts\RoutingServiceInterface;
use App\Infrastructure\Routing\Infrastructure\Contracts\RouteDispatcherInterface;
use App\Infrastructure\Routing\Infrastructure\Contracts\RouteResolverInterface;
use App\Infrastructure\Routing\Infrastructure\Exceptions\RouteNotFoundException;
use App\Infrastructure\Routing\Infrastructure\Exceptions\MethodNotAllowedException;
use App\Infrastructure\Routing\Infrastructure\Exceptions\RouteDispatchException;
use App\Infrastructure\Routing\Presentation\Http\Contracts\ServerRequestInterface;
use App\Infrastructure\Routing\Domain\Events\Contracts\RoutingEventInterface;
use App\Infrastructure\Routing\Domain\Events\RouteNotFoundEvent;
use App\Infrastructure\Routing\Domain\Events\RouteMatchedEvent;
use App\Infrastructure\Routing\Domain\Events\RouteResolvedEvent;
use App\Infrastructure\Routing\Domain\Events\BeforeRouteDispatchEvent;
use App\Infrastructure\Routing\Domain\Events\AfterRouteDispatchEvent;
use App\Infrastructure\Routing\Domain\Events\RouteDispatchFailedEvent;

use Throwable;

/**
 * RoutingService
 *
 * Central entry point for processing HTTP requests through the routing system.
 * Emits and stores domain events for external processing.
 */
class RoutingService implements RoutingServiceInterface
{
    private RouteResolverInterface $resolver;
    private RouteDispatcherInterface $dispatcher;

    /** @var RoutingEventInterface[] */
    private array $recordedEvents = [];

    public function __construct(
        RouteResolverInterface $resolver,
        RouteDispatcherInterface $dispatcher
    ) {
        $this->resolver = $resolver;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handles the incoming HTTP route request.
     * Emits and records events for external dispatch.
     *
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function handle(ServerRequestInterface $request): mixed
    {
        $route = null;
        $controllerAction = null;

        try {
            $route = $this->resolver->resolve($request);

            $this->recordEvent(new RouteResolvedEvent($request, $route));
            $this->recordEvent(new RouteMatchedEvent($request, $route));

            $controllerAction = $route->controllerAction();
            $this->recordEvent(new BeforeRouteDispatchEvent($request, $route, $controllerAction));

            $result = $this->dispatcher->dispatch($route, $request);

            $this->recordEvent(new AfterRouteDispatchEvent($request, $route, $controllerAction, $result));

            return $result;

        } catch (RouteNotFoundException $e) {
            $this->recordEvent(new RouteNotFoundEvent($request, $e->getMessage()));
            return $this->handleNotFound();

        } catch (MethodNotAllowedException $e) {
            return $this->handleMethodNotAllowed();

        } catch (RouteDispatchException $e) {
            $this->recordEvent(new RouteDispatchFailedEvent(
                $request,
                $e,
                $route,
                $controllerAction
            ));
            return $this->handleInternalError();

        } catch (\Throwable $e) {
            $this->recordEvent(new RouteDispatchFailedEvent(
                $request,
                $e,
                $route,
                $controllerAction
            ));
            return $this->handleInternalError();
        }
    }

    /**
     * Records a domain event for later external dispatch.
     *
     * @param RoutingEventInterface $event
     * @return void
     */
    protected function recordEvent(RoutingEventInterface $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /**
     * Returns all recorded events without clearing them.
     *
     * @return RoutingEventInterface[]
     */
    public function recordedEvents(): array
    {
        return $this->recordedEvents;
    }

    /**
     * Releases and clears all recorded events for external dispatch.
     *
     * @return RoutingEventInterface[]
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    /**
     * Returns the standard response for a 404 Not Found error.
     *
     * @return array
     */
    protected function handleNotFound(): array
    {
        return [
            'status' => 404,
            'error'  => 'Not Found',
        ];
    }

    /**
     * Returns the standard response for a 405 Method Not Allowed error.
     *
     * @return array
     */
    protected function handleMethodNotAllowed(): array
    {
        return [
            'status' => 405,
            'error'  => 'Method Not Allowed',
        ];
    }

    /**
     * Returns the standard response for a 500 Internal Server Error.
     *
     * @return array
     */
    protected function handleInternalError(): array
    {
        return [
            'status' => 500,
            'error'  => 'Internal Server Error',
        ];
    }
}
