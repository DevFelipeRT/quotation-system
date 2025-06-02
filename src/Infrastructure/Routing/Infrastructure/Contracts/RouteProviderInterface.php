<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Contracts;

/**
 * Interface RouteProviderInterface
 *
 * Defines the contract for classes that expose route definitions
 * to be later registered into a routing repository.
 */
interface RouteProviderInterface
{
    /**
     * Returns all route definitions provided by this source.
     *
     * @return HttpRouteInterface[]
     */
    public function provideRoutes(): array;
}
