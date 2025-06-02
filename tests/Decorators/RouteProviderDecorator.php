<?php

declare(strict_types=1);

namespace Tests\Decorators;

use App\Infrastructure\Routing\Infrastructure\Contracts\RouteProviderInterface;
use App\Infrastructure\Routing\Presentation\Http\HttpRoute;
use App\Infrastructure\Routing\Domain\ValueObjects\HttpMethod;
use App\Infrastructure\Routing\Domain\ValueObjects\RoutePath;
use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;
use Tests\Controllers\FakeController;

class RouteProviderDecorator implements RouteProviderInterface
{
    public function provideRoutes(): array
    {
        return [
            new HttpRoute(
                new HttpMethod('GET'),
                new RoutePath('/hello'),
                new ControllerAction(FakeController::class, 'hello'),
                'hello_route'
            ),
        ];
    }
}
