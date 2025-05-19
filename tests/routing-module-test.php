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

echo "<pre>";

// ======== Diagnostic Utility ========
function printStatus(string $message, string $status = 'INFO'): void
{
    echo sprintf("[%s] %s%s", strtoupper($status), $message, PHP_EOL);
}

// ======== STEP 1: Bootstrap configuration ========
$configContainer = require_once __DIR__ . '/test-bootstrap.php';
printStatus("Bootstrap executed successfully. Configuration container loaded.", 'STEP');

// ======== STEP 2: Initialize Logging (opcional) ========
$logger = null;
try {
    if (class_exists('App\Kernel\Infrastructure\LoggingKernel')) {
        $loggingKernel = new LoggingKernel($configContainer);
        $logger = $loggingKernel->getLoggerAdapter('psr');
        printStatus("LoggingKernel initialized with PSR logger.", 'OK');
    }
} catch (Throwable $e) {
    printStatus("Failed to initialize LoggingKernel: {$e->getMessage()}", 'FAIL');
}

// ======== STEP 3: Register Routing Kernel ========
try {
    $controllerMap = [
        HomeTestController::class => new HomeTestController()
    ];
    // Passa o FQCN do controller de teste para o provider Home
    $controllerClassMap = [
        HomeRouteProvider::class => HomeTestController::class
    ];

    $kernel = new RouterKernel($controllerMap, $controllerClassMap);
    $routingEngine = $kernel->engine();
    printStatus("Routing kernel initialized.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize routing kernel: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// ======== STEP 4: Test Requests (Happy Path) ========
$requests = [
    new RouteRequest(new HttpMethod('GET'), new RoutePath('/'), 'localhost', 'http'),
    new RouteRequest(new HttpMethod('GET'), new RoutePath('/home'), 'localhost', 'http'),
    new RouteRequest(new HttpMethod('GET'), new RoutePath('/quotationManager'), 'localhost', 'http'),
];

foreach ($requests as $i => $request) {
    try {
        $response = $routingEngine->handle($request);
        printStatus("Request #{$i} dispatched successfully. Response: " . var_export($response, true), 'OK');
    } catch (Throwable $e) {
        printStatus("Request #{$i} dispatch failed: {$e->getMessage()}", 'FAIL');
    }
}

// ======== STEP 5: Test Not Found and Method Not Allowed ========
$notFoundRequest = new RouteRequest(new HttpMethod('GET'), new RoutePath('/inexistent'), 'localhost', 'http');
try {
    $routingEngine->handle($notFoundRequest);
    printStatus("NotFound request was incorrectly dispatched!", 'FAIL');
} catch (Throwable $e) {
    printStatus("Correctly handled not found route: {$e->getMessage()}", 'RESULT');
}

$methodNotAllowedRequest = new RouteRequest(new HttpMethod('POST'), new RoutePath('/home'), 'localhost', 'http');
try {
    $routingEngine->handle($methodNotAllowedRequest);
    printStatus("MethodNotAllowed request was incorrectly dispatched!", 'FAIL');
} catch (Throwable $e) {
    printStatus("Correctly handled method not allowed: {$e->getMessage()}", 'RESULT');
}

// ======== STEP 6: Test Dynamic Route Addition ========
try {
    // Para adicionar rotas dinâmicas, acesse o repository via reflection (padrão clean: preferir extensões no kernel se necessário)
    $kernelReflection = new \ReflectionClass($kernel);
    $providersProp = $kernelReflection->getProperty('providers');
    $providersProp->setAccessible(true);
    $providers = $providersProp->getValue($kernel);

    // Procura o repositório na engine
    $engineReflection = new \ReflectionClass($routingEngine);
    $resolverProp = $engineReflection->getProperty('resolver');
    $resolverProp->setAccessible(true);
    $resolver = $resolverProp->getValue($routingEngine);

    $resolverReflection = new \ReflectionClass($resolver);
    $repositoryProp = $resolverReflection->getProperty('repository');
    $repositoryProp->setAccessible(true);
    $routeRepository = $repositoryProp->getValue($resolver);

    // Adiciona a rota custom diretamente ao repositório
    $customRoute = new HttpRoute(
        new HttpMethod('GET'),
        new RoutePath('/custom'),
        new ControllerAction(HomeTestController::class, 'handleCustom'),
        'home.custom'
    );
    $routeRepository->add($customRoute);

    $customRequest = new RouteRequest(new HttpMethod('GET'), new RoutePath('/custom'), 'localhost', 'http');
    $response = $routingEngine->handle($customRequest);
    printStatus("Dynamic route dispatched. Response: " . var_export($response, true), 'OK');
} catch (Throwable $e) {
    printStatus("Failed to dispatch dynamic route: {$e->getMessage()}", 'FAIL');
}

// ======== END ========
printStatus("Routing test completed.", 'END');
echo "</pre>";