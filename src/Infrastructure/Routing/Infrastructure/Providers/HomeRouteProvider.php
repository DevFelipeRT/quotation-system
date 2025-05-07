<?php

namespace App\Infrastructure\Routing\Providers;

use App\Application\Routing\RoutePath;
use App\Infrastructure\Routing\Contracts\RouteProviderInterface;
use App\Presentation\Http\Controllers\HomeController;
use App\Presentation\Http\Routing\ControllerAction;
use App\Presentation\Http\Routing\Contracts\HttpRouteInterface;
use App\Presentation\Http\Routing\HttpMethod;
use App\Presentation\Http\Routing\HttpRoute;

/**
 * HomeRouteProvider
 *
 * Provides all HTTP routes related to the Home module.
 * Used during application bootstrap to declare route definitions.
 */
final class HomeRouteProvider implements RouteProviderInterface
{
    /**
     * Returns all static route definitions provided by this module.
     *
     * @return HttpRouteInterface[]
     */
    public function provideRoutes(): array
    {
        return [
            $this->makeRoute('GET', '/', HomeController::class, 'handle', 'home.index'),
            $this->makeRoute('GET', '/home', HomeController::class, 'handle', 'home.index'),
            $this->makeRoute('GET', '/quotationManager', HomeController::class, 'handle', 'quotationManager.index'),
        ];
    }

    /**
     * Instantiates a route with the given parameters.
     */
    private function makeRoute(
        string $method,
        string $path,
        string $controllerClass,
        string $actionMethod,
        string $name
    ): HttpRouteInterface {
        return new HttpRoute(
            new HttpMethod($method),
            new RoutePath($path),
            new ControllerAction($controllerClass, $actionMethod),
            $name
        );
    }
}
