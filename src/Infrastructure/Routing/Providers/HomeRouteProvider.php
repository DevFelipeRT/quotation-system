<?php

namespace App\Infrastructure\Routing\Providers;

use App\Application\Routing\RoutePath;
use App\Interfaces\Application\Routing\RouteRepositoryInterface;
use App\Interfaces\Presentation\Routing\RouteProviderInterface;
use App\Presentation\Http\Controllers\HomeController;
use App\Presentation\Http\Routing\ControllerAction;
use App\Presentation\Http\Routing\HttpMethod;
use App\Presentation\Http\Routing\HttpRoute;

/**
 * HomeRouteProvider
 *
 * Registers HTTP routes related to the Home module.
 * This provider encapsulates route declarations for use during application bootstrap.
 *
 * @implements RouteProviderInterface
 */
final class HomeRouteProvider implements RouteProviderInterface
{
    /**
     * Registers all routes into the provided repository.
     *
     * @param RouteRepositoryInterface $repository
     * @return void
     */
    public function registerRoutes(RouteRepositoryInterface $repository): void
    {
        $route = new HttpRoute(
            new HttpMethod('GET'),
            new RoutePath('/'),
            new ControllerAction(HomeController::class, 'handle'),
            'home.index'
        );

        $repository->add($route);

        $route = new HttpRoute(
            new HttpMethod('GET'),
            new RoutePath('/home'),
            new ControllerAction(HomeController::class, 'handle'),
            'home.index'
        );

        $repository->add($route);

        $route = new HttpRoute(
            new HttpMethod('GET'),
            new RoutePath('/quotationManager'),
            new ControllerAction(HomeController::class, 'handle'),
            'quotationManager.index'
        );

        $repository->add($route);
    }
}
