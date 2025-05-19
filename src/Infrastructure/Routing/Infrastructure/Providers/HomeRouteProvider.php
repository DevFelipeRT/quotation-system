<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Providers;

use App\Infrastructure\Routing\Domain\Contracts\RouteProviderInterface;
use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;
use App\Infrastructure\Routing\Domain\ValueObjects\HttpMethod;
use App\Infrastructure\Routing\Domain\ValueObjects\RoutePath;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\HttpRoute;
use App\Presentation\Http\Controllers\HomeController;

/**
 * HomeRouteProvider
 *
 * Provides all HTTP routes related to the Home module.
 * Used during application bootstrap to declare route definitions.
 * The controller FQCN can be injected, allowing for testing with custom controllers.
 */
final class HomeRouteProvider implements RouteProviderInterface
{
    /**
     * The fully qualified class name (FQCN) of the controller for these routes.
     * @var class-string
     */
    private string $controllerClass;

    /**
     * HomeRouteProvider constructor.
     *
     * @param string $controllerClass FQCN of the controller (defaults to HomeController::class)
     */
    public function __construct(string $controllerClass = HomeController::class)
    {
        $this->controllerClass = $controllerClass;
    }

    /**
     * Returns all static route definitions provided by this module.
     *
     * @return HttpRouteInterface[]
     */
    public function provideRoutes(): array
    {
        return [
            $this->makeRoute('GET', '/', $this->controllerClass, 'handle', 'home.index'),
            $this->makeRoute('GET', '/home', $this->controllerClass, 'handle', 'home.index'),
            $this->makeRoute('GET', '/quotationManager', $this->controllerClass, 'handle', 'quotationManager.index'),
        ];
    }

    /**
     * Instantiates a route with the given parameters.
     *
     * @param string $method
     * @param string $path
     * @param string $controllerClass
     * @param string $actionMethod
     * @param string $name
     * @return HttpRouteInterface
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
