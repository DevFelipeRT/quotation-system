<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Presentation\Http\Routing;

use App\Infrastructure\Routing\Application\Services\RoutePath;
use App\Infrastructure\Routing\Presentation\Http\Routing\Contracts\HttpRouteInterface;

/**
 * Class HttpRoute
 *
 * Default implementation of HttpRouteInterface.
 */
final class HttpRoute implements HttpRouteInterface
{
    private readonly HttpMethod $method;
    private readonly RoutePath $path;
    private readonly ControllerAction $controllerAction;
    private readonly string $name;

    /**
     * @param HttpMethod $method
     * @param RoutePath $path
     * @param ControllerAction $controllerAction
     * @param string $name
     */
    public function __construct(
        HttpMethod $method,
        RoutePath $path,
        ControllerAction $controllerAction,
        string $name
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->controllerAction = $controllerAction;
        $this->name = $name;
    }

    public function method(): HttpMethod
    {
        return $this->method;
    }

    public function path(): RoutePath
    {
        return $this->path;
    }

    public function controllerAction(): ControllerAction
    {
        return $this->controllerAction;
    }

    public function name(): string
    {
        return $this->name;
    }
}