<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Contracts;

/**
 * Contract for a factory responsible for creating controller instances.
 */
interface ControllerFactoryInterface
{
    /**
     * Creates and returns a new controller instance for the specified class.
     *
     * @param string $controllerClass
     * @return object
     * @throws \App\Infrastructure\Routing\Infrastructure\Exceptions\RouteDispatchException
     */
    public function create(string $controllerClass): object;
}
