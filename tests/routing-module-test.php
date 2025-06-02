<?php

declare(strict_types=1);

use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;
use App\Infrastructure\Routing\Domain\ValueObjects\HttpMethod;
use App\Infrastructure\Routing\Domain\ValueObjects\RoutePath;
use App\Infrastructure\Routing\Infrastructure\Exceptions\MethodNotAllowedException;
use App\Infrastructure\Routing\Infrastructure\Exceptions\RouteNotFoundException;
use App\Infrastructure\Routing\Infrastructure\RoutingKernel;
use App\Infrastructure\Routing\Presentation\Http\HttpRoute;
use App\Infrastructure\Routing\Presentation\Http\ServerRequest;
use App\Shared\Container\Infrastructure\ContainerKernel;
use App\Shared\Discovery\Infrastructure\DiscoveryKernel;
use Tests\Controllers\FakeController;
use Tests\Controllers\HomeTestController;
use Tests\Decorators\RouteProviderDecorator;

require_once __DIR__ . '/test-bootstrap.php';
require_once BASE_PATH . '/autoload.php';

echo "<pre>";

function printStatus(string $message, string $status = 'INFO'): void
{
    echo sprintf("[%s] %s%s", strtoupper($status), $message, PHP_EOL);
}

try {
    // 1. Setup: container e scanner
    $container = ContainerKernel::container();
    $scanner = (new DiscoveryKernel("App\\", SRC_DIR))->facade();

    printStatus("Container and Discovery scanner initialized.", 'STEP');

    // 2. Bind controller manualmente no container (autowiring alternativo)
    $container->bind(FakeController::class, fn () => new FakeController());
    $container->bind(HomeTestController::class, fn () => new HomeTestController());

    // 3. Inicializa o RoutingKernel com dependências injetadas
    $kernel = new RoutingKernel($scanner, $container);
    $kernel->registerProviders(new RouteProviderDecorator());
    $kernel->boot();

    printStatus("Routing kernel booted successfully.", 'OK');

    // 4. Testes com rotas reais mapeadas
    $requests = [
        new ServerRequest(new HttpMethod('GET'), new RoutePath('/'), 'localhost', 'http'),
        new ServerRequest(new HttpMethod('GET'), new RoutePath('/home'), 'localhost', 'http'),
    ];

    foreach ($requests as $i => $request) {
        try {
            $response = $kernel->engine()->handle($request);
            printStatus("Request #{$i} dispatched successfully. Response: " . var_export($response, true), 'OK');
        } catch (Throwable $e) {
            printStatus("Request #{$i} failed: {$e->getMessage()}", 'FAIL');
        }
    }

    // 5. Rota inexistente
    $invalidRequest = new ServerRequest(new HttpMethod('GET'), new RoutePath('/inexistent'), 'localhost', 'http');
    try {
        $kernel->engine()->handle($invalidRequest);
        printStatus("Invalid route was incorrectly handled.", 'FAIL');
    } catch (RouteNotFoundException) {
        printStatus("Correctly rejected nonexistent route.", 'RESULT');
    } catch (Throwable $e) {
        printStatus("Unexpected error on invalid route: {$e->getMessage()}", 'FAIL');
    }

    // 6. Método não permitido
    $notAllowedRequest = new ServerRequest(new HttpMethod('POST'), new RoutePath('/home'), 'localhost', 'http');
    try {
        $kernel->engine()->handle($notAllowedRequest);
        printStatus("Dispatched POST to GET-only route!", 'FAIL');
    } catch (MethodNotAllowedException) {
        printStatus("Correctly rejected method not allowed.", 'RESULT');
    } catch (Throwable $e) {
        printStatus("Unexpected error on method restriction: {$e->getMessage()}", 'FAIL');
    }

    // 7. Rota dinâmica adicionada diretamente no repositório
    $repository = (fn () => $this->repository)->call($kernel);
    $dynamicRoute = new HttpRoute(
        new HttpMethod('GET'),
        new RoutePath('/custom'),
        new ControllerAction(HomeTestController::class, 'handleCustom'),
        'home.custom'
    );
    $repository->add($dynamicRoute);

    $customRequest = new ServerRequest(new HttpMethod('GET'), new RoutePath('/custom'), 'localhost', 'http');
    $customResponse = $kernel->engine()->handle($customRequest);
    printStatus("Dynamic route dispatched. Response: " . var_export($customResponse, true), 'OK');

    // 8. Eventos registrados durante o roteamento
    $events = $kernel->engine()->recordedEvents();
    printStatus("Total routing events recorded: " . count($events), 'INFO');
    foreach ($events as $i => $event) {
        printStatus("Event #{$i}: " . get_class($event), 'EVENT');
    }

    printStatus("Routing integration test completed.", 'END');

} catch (Throwable $e) {
    printStatus("Critical error: {$e->getMessage()}", 'FATAL');
    echo $e;
}

echo "</pre>";
