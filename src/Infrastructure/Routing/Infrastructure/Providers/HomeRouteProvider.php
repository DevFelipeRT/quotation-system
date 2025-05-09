<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Providers;

use App\Infrastructure\Routing\Application\Services\RoutePath;
use App\Infrastructure\Routing\Domain\Contracts\RouteProviderInterface;
use App\Infrastructure\Routing\Presentation\Http\Routing\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\Routing\ControllerAction;
use App\Infrastructure\Routing\Presentation\Http\Routing\HttpMethod;
use App\Infrastructure\Routing\Presentation\Http\Routing\HttpRoute;
use App\Presentation\Http\Controllers\HomeController;

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
