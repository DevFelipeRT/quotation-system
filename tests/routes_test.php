<?php

declare(strict_types=1);

use App\Application\Routing\RoutePath;
use App\Application\Routing\RoutingEngine;
use App\Infrastructure\Routing\Dispatcher\DefaultRouteDispatcher;
use App\Infrastructure\Routing\Matcher\DefaultRouteMatcher;
use App\Infrastructure\Routing\Providers\StaticRouteProvider;
use App\Infrastructure\Routing\Repository\InMemoryRouteRepository;
use App\Infrastructure\Routing\Resolver\DefaultRouteResolver;
use App\Presentation\Http\Routing\ControllerAction;
use App\Presentation\Http\Routing\HttpMethod;
use App\Presentation\Http\Routing\HttpRoute;
use App\Presentation\Http\Routing\RouteRequest;

require_once __DIR__ . '/../autoload.php';


/**
 * Simulated controller for routing test.
 */
final class TestController
{
    public function hello(): string
    {
        return 'Hello from the Test Controller!';
    }
}

// ============================================
// Setup: Controller map (simula injeção DI)
// ============================================

$controller = new TestController();
$controllerMap = [
    TestController::class => $controller,
];

// ============================================
// Routing Components
// ============================================

$route = new HttpRoute(
    new HttpMethod('GET'),
    new RoutePath('/hello'),
    new ControllerAction(TestController::class, 'hello'),
    'test.hello'
);

$repository = new InMemoryRouteRepository();
$provider = new StaticRouteProvider([$route]);
$provider->registerRoutes($repository);

$matcher = new DefaultRouteMatcher();
$resolver = new DefaultRouteResolver($repository, $matcher);
$dispatcher = new DefaultRouteDispatcher($resolver, $controllerMap);

$engine = new RoutingEngine($resolver, $dispatcher);

// ============================================
// Simulate an HTTP Request
// ============================================

$request = new RouteRequest(
    new HttpMethod('GET'),
    new RoutePath('/hello'),
    'localhost',
    'http'
);

// ============================================
// Handle and Output
// ============================================

try {
    echo $engine->handle($request);
} catch (Throwable $e) {
    echo "[500] Internal Server Error\n";
    echo $e->getMessage();
}
