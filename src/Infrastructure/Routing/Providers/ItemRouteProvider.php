<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Providers;

use App\Application\Routing\RoutePath;
use App\Interfaces\Application\Routing\RouteRepositoryInterface;
use App\Interfaces\Presentation\Routing\RouteProviderInterface;
use App\Presentation\Http\Controllers\Item\Controller;
use App\Presentation\Http\Controllers\Item\CreateController;
use App\Presentation\Http\Controllers\Item\UpdateController;
use App\Presentation\Http\Controllers\Item\DeleteController;
use App\Presentation\Http\Routing\HttpMethod;
use App\Presentation\Http\Routing\HttpRoute;
use App\Presentation\Http\Routing\ControllerAction;

/**
 * Class ItemRouteProvider
 *
 * Registers all HTTP routes related to item management.
 * Each route is mapped to a specific controller and action.
 */
final class ItemRouteProvider implements RouteProviderInterface
{
    /**
     * Registers all item-related routes to the repository.
     *
     * @param RouteRepositoryInterface $repository
     * @return void
     */
    public function registerRoutes(RouteRepositoryInterface $repository): void
    {
        // Interface principal de gerenciamento de itens
        $repository->add(new HttpRoute(
            new HttpMethod('GET'),
            new RoutePath('/itemsManager'),
            new ControllerAction(Controller::class, 'index'),
            'items.index'
        ));

        // Criação de item
        $repository->add(new HttpRoute(
            new HttpMethod('POST'),
            new RoutePath('/itemsManager/create'),
            new ControllerAction(CreateController::class, '__invoke'),
            'items.create'
        ));

        // Atualização de item
        $repository->add(new HttpRoute(
            new HttpMethod('POST'),
            new RoutePath('/itemsManager/update'),
            new ControllerAction(UpdateController::class, '__invoke'),
            'items.update'
        ));

        // Remoção de item
        $repository->add(new HttpRoute(
            new HttpMethod('POST'),
            new RoutePath('/itemsManager/delete'),
            new ControllerAction(DeleteController::class, '__invoke'),
            'items.delete'
        ));
    }
}
