<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Providers;

use App\Domains\Item\Presentation\Http\Controllers\Controller;
use App\Domains\Item\Presentation\Http\Controllers\CreateController;
use App\Domains\Item\Presentation\Http\Controllers\DeleteController;
use App\Domains\Item\Presentation\Http\Controllers\UpdateController;
use App\Infrastructure\Routing\Domain\Contracts\RouteProviderInterface;
use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;
use App\Infrastructure\Routing\Domain\ValueObjects\HttpMethod;
use App\Infrastructure\Routing\Domain\ValueObjects\RoutePath;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\HttpRoute;

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
