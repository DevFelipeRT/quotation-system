<?php

use App\Kernel\Infrastructure\RouterKernel;
use App\Infrastructure\Routing\Presentation\Http\RouteRequest;
use App\Infrastructure\Routing\Domain\ValueObjects\HttpMethod;
use App\Infrastructure\Routing\Domain\ValueObjects\RoutePath;
use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;
use App\Infrastructure\Routing\Infrastructure\Providers\HomeRouteProvider;
use App\Infrastructure\Routing\Presentation\Http\HttpRoute;
use App\Kernel\Infrastructure\LoggingKernel;
use Tests\Controllers\HomeTestController;
use App\Kernel\Adapters\Providers\RoutingEventBindingProvider;

// ========================
// Diagnostic Utility
// ========================
function printStatus(string $message, string $status = 'INFO'): void
{
    echo sprintf("[%s] %s%s", strtoupper($status), $message, PHP_EOL);
}

echo "<pre>";

// ========================
// STEP 1: Bootstrap configuration
// ========================
$configContainer = require_once __DIR__ . '/test-bootstrap.php';
printStatus("Bootstrap executed successfully. Configuration container loaded.", 'STEP');

// ========================
// STEP 2: Initialize Logging (REAL LOGGER)
// ========================
$logger = null;
try {
    $loggingKernel = new LoggingKernel($configContainer);
    $logger = $loggingKernel->getLoggerAdapter('psr');
    printStatus("LoggingKernel initialized with PSR logger.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize LoggingKernel: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// ========================
// STEP 3: Set up event dispatcher and listeners
// ========================
class TestEventDispatcher implements \App\Shared\Event\Contracts\EventDispatcherInterface
{
    /** @var array<class-string, callable[]> */
    private array $listeners = [];
    public array $events = [];

    public function addListener(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(object $event): void
    {
        $this->events[] = $event;
        foreach ($this->listeners as $eventClass => $listeners) {
            if ($event instanceof $eventClass) {
                foreach ($listeners as $listener) {
                    $listener($event);
                }
            }
        }
    }
}

$eventDispatcher = new TestEventDispatcher();
$routingBindingProvider = new RoutingEventBindingProvider($logger);

foreach ($routingBindingProvider->bindings() as $eventClass => $listeners) {
    foreach ($listeners as $listener) {
        $eventDispatcher->addListener($eventClass, $listener);
    }
}
printStatus("Event listeners bound to dispatcher.", 'OK');

// ========================
// STEP 4: Initialize Routing Kernel (with event dispatcher)
// ========================
try {
    $controllerMap = [
        HomeTestController::class => new HomeTestController()
    ];
    $controllerClassMap = [
        HomeRouteProvider::class => HomeTestController::class
    ];
    $kernel = new RouterKernel($controllerMap, $controllerClassMap, $eventDispatcher);
    printStatus("Routing kernel initialized.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize routing kernel: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// ========================
// STEP 5: Test Requests (Happy Path)
// ========================
$requests = [
    new RouteRequest(new HttpMethod('GET'), new RoutePath('/'), 'localhost', 'http'),
    new RouteRequest(new HttpMethod('GET'), new RoutePath('/home'), 'localhost', 'http'),
    new RouteRequest(new HttpMethod('GET'), new RoutePath('/quotationManager'), 'localhost', 'http'),
];

foreach ($requests as $i => $request) {
    try {
        $response = $kernel->dispatch($request);
        printStatus("Request #{$i} dispatched successfully. Response: " . var_export($response, true), 'OK');
    } catch (Throwable $e) {
        printStatus("Request #{$i} dispatch failed: {$e->getMessage()}", 'FAIL');
    }
}

// ========================
// STEP 6: Test Not Found and Method Not Allowed
// ========================
$notFoundRequest = new RouteRequest(new HttpMethod('GET'), new RoutePath('/inexistent'), 'localhost', 'http');
try {
    $kernel->dispatch($notFoundRequest);
    printStatus("NotFound request was incorrectly dispatched!", 'FAIL');
} catch (Throwable $e) {
    printStatus("Correctly handled not found route: {$e->getMessage()}", 'RESULT');
}

$methodNotAllowedRequest = new RouteRequest(new HttpMethod('POST'), new RoutePath('/home'), 'localhost', 'http');
try {
    $kernel->dispatch($methodNotAllowedRequest);
    printStatus("MethodNotAllowed request was incorrectly dispatched!", 'FAIL');
} catch (Throwable $e) {
    printStatus("Correctly handled method not allowed: {$e->getMessage()}", 'RESULT');
}

// ========================
// STEP 7: Test Dynamic Route Addition
// ========================
try {
    $kernelReflection = new \ReflectionClass($kernel);
    $resolverProp = $kernelReflection->getProperty('resolver');
    $resolverProp->setAccessible(true);
    $resolver = $resolverProp->getValue($kernel);

    $resolverReflection = new \ReflectionClass($resolver);
    $repositoryProp = $resolverReflection->getProperty('repository');
    $repositoryProp->setAccessible(true);
    $routeRepository = $repositoryProp->getValue($resolver);

    $customRoute = new HttpRoute(
        new HttpMethod('GET'),
        new RoutePath('/custom'),
        new ControllerAction(HomeTestController::class, 'handleCustom'),
        'home.custom'
    );
    $routeRepository->add($customRoute);

    $customRequest = new RouteRequest(new HttpMethod('GET'), new RoutePath('/custom'), 'localhost', 'http');
    $response = $kernel->dispatch($customRequest);
    printStatus("Dynamic route dispatched. Response: " . var_export($response, true), 'OK');
} catch (Throwable $e) {
    printStatus("Failed to dispatch dynamic route: {$e->getMessage()}", 'FAIL');
}

// ========================
// STEP 8: Event/Listener Validation
// ========================
printStatus("Total events dispatched: " . count($eventDispatcher->events), 'INFO');
foreach ($eventDispatcher->events as $i => $event) {
    printStatus("Event #{$i}: " . get_class($event), 'EVENT');
}

printStatus("Routing test completed.", 'END');
echo "</pre>";
