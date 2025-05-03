<?php

namespace App\Interfaces\Presentation\Routing;

use App\Application\Routing\RoutePath;
use App\Presentation\Http\Routing\ControllerAction;
use App\Presentation\Http\Routing\HttpMethod;

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
