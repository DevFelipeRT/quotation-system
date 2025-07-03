<?php

declare(strict_types=1);

namespace Routing\Presentation\Http\Contracts;

use Routing\Domain\ValueObjects\ControllerAction;
use Routing\Domain\ValueObjects\HttpMethod;
use Routing\Domain\ValueObjects\RoutePath;

/**
 * Interface HttpRouteInterface
 *
 * Defines the contract for an HTTP route containing the method, path, and target controller action.
 */
interface HttpRouteInterface
{
    /**
     * Returns the HTTP method associated with this route (e.g., GET, POST).
     *
     * @return HttpMethod
     */
    public function method(): HttpMethod;

    /**
     * Returns the path pattern of the route (e.g., "/users/{id}").
     *
     * @return RoutePath
     */
    public function path(): RoutePath;

    /**
     * Returns the controller action to be executed when this route is matched.
     *
     * @return ControllerAction
     */
    public function controllerAction(): ControllerAction;

    /**
     * Returns the name identifier of this route.
     *
     * @return string
     */
    public function name(): string;
}
