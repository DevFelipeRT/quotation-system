<?php

declare(strict_types=1);

use App\Infrastructure\Routing\Presentation\Http\RouteRequest;
use App\Infrastructure\Routing\Domain\ValueObjects\HttpMethod;
use App\Infrastructure\Routing\Domain\ValueObjects\RoutePath;
use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;
use App\Infrastructure\Routing\Presentation\Http\HttpRoute;
use Tests\Controllers\HomeTestController;
use App\Kernel\KernelManager;
use App\Infrastructure\Routing\Infrastructure\Exceptions\RouteNotFoundException;
use App\Infrastructure\Routing\Infrastructure\Exceptions\MethodNotAllowedException;

function printStatus(string $message, string $status = 'INFO'): void
{
    echo sprintf("[%s] %s%s", strtoupper($status), $message, PHP_EOL);
}

function runIntegrationTestWithKernelManager(): void
{
    echo "<pre>";

    $configProvider = require_once __DIR__ . '/test-bootstrap.php';
    printStatus("Bootstrap executed successfully. Configuration provider loaded.", 'STEP');

    try {
        $kernelManager = new KernelManager($configProvider);
        printStatus("KernelManager initialized.", 'OK');
    } catch (Throwable $e) {
        printStatus("Fatal error on KernelManager: " . $e->getMessage(), 'FAIL');
        echo $e;
        exit(1);
    }

    $eventDispatcher = null;
    try {
        $eventDispatcher = $kernelManager->getEventListeningKernel()->dispatcher();
        printStatus("Event dispatcher initialized successfully.", 'OK');
    } catch (Throwable $e) {
        printStatus("Failed to initialize event dispatcher: {$e->getMessage()}", 'FAIL');
        exit(1);
    }

    try {
        $kernel = $kernelManager->getRouterKernel();
        printStatus("Routing kernel initialized.", 'OK');
    } catch (Throwable $e) {
        printStatus("Failed to initialize routing kernel: {$e->getMessage()}", 'FAIL');
        exit(1);
    }

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

    $notFoundRequest = new RouteRequest(new HttpMethod('GET'), new RoutePath('/inexistent'), 'localhost', 'http');
    try {
        $kernel->dispatch($notFoundRequest);
        printStatus("NotFound request was incorrectly dispatched!", 'FAIL');
    } catch (Throwable $e) {
        if ($e instanceof RouteNotFoundException) {
            printStatus("Correctly handled not found route.", 'RESULT');
        } else {
            printStatus("Unexpected error during not found route: {$e->getMessage()}", 'FAIL');
        }
    }

    $methodNotAllowedRequest = new RouteRequest(new HttpMethod('POST'), new RoutePath('/home'), 'localhost', 'http');
    try {
        $kernel->dispatch($methodNotAllowedRequest);
        printStatus("MethodNotAllowed request was incorrectly dispatched!", 'FAIL');
    } catch (Throwable $e) {
        if ($e instanceof MethodNotAllowedException) {
            printStatus("Correctly handled method not allowed.", 'RESULT');
        } else {
            printStatus("Unexpected error during method not allowed: {$e->getMessage()}", 'FAIL');
        }
    }

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

    printStatus("Total events dispatched: " . count($eventDispatcher->events), 'INFO');
    foreach ($eventDispatcher->events as $i => $event) {
        printStatus("Event #{$i}: " . get_class($event), 'EVENT');
    }

    printStatus("Routing test completed.", 'END');
    echo "</pre>";
}

runIntegrationTestWithKernelManager();
