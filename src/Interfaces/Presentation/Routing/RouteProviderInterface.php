<?php

namespace App\Interfaces\Presentation\Routing;

use App\Interfaces\Application\Routing\RouteRepositoryInterface;

/**
 * Interface RouteProviderInterface
 *
 * Defines a contract for classes that register routes into the application's route repository.
 */
interface RouteProviderInterface
{
    /**
     * Registers a set of routes into the given route repository.
     *
     * @param RouteRepositoryInterface $repository The route repository where routes should be registered.
     * @return void
     */
    public function registerRoutes(RouteRepositoryInterface $repository): void;
}
