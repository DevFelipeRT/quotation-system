<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Providers;

use App\Application\Routing\RoutePath;
use App\Infrastructure\Routing\Contracts\RouteProviderInterface;
use App\Presentation\Http\Controllers\Item\Controller;
use App\Presentation\Http\Controllers\Item\CreateController;
use App\Presentation\Http\Controllers\Item\UpdateController;
use App\Presentation\Http\Controllers\Item\DeleteController;
use App\Presentation\Http\Routing\ControllerAction;
use App\Presentation\Http\Routing\Contracts\HttpRouteInterface;
use App\Presentation\Http\Routing\HttpMethod;
use App\Presentation\Http\Routing\HttpRoute;

/**
 * ItemRouteProvider
 *
 * Provides HTTP route definitions for item management operations.
 */
final class ItemRouteProvider implements RouteProviderInterface
{
    /**
     * Returns all route definitions for the Item module.
     *
     * @return HttpRouteInterface[]
     */
    public function provideRoutes(): array
    {
        return [
            $this->makeRoute('GET', '/itemsManager', Controller::class, 'index', 'items.index'),
            $this->makeRoute('POST', '/itemsManager/create', CreateController::class, '__invoke', 'items.create'),
            $this->makeRoute('POST', '/itemsManager/update', UpdateController::class, '__invoke', 'items.update'),
            $this->makeRoute('POST', '/itemsManager/delete', DeleteController::class, '__invoke', 'items.delete'),
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
