<?php 

declare(strict_types=1);

use App\Infrastructure\Routing\Domain\ValueObjects\HttpMethod;
use App\Infrastructure\Routing\Domain\ValueObjects\RoutePath;
use App\Infrastructure\Routing\Infrastructure\ControllerFactory;
use App\Infrastructure\Routing\Infrastructure\RoutingKernel;
use App\Infrastructure\Routing\Presentation\Http\ServerRequest;
use App\Shared\Container\Infrastructure\ContainerKernel;
use App\Shared\Discovery\Infrastructure\DiscoveryKernel;
use Tests\Controllers\FakeController;
use Tests\Decorators\RouteProviderDecorator;

require_once __DIR__ . '/test-bootstrap.php';
require_once BASE_PATH . '/autoload.php';
echo "<pre>";

// 1. Crie um container e um scanner
$container = ContainerKernel::container();

$discoveryKernel = new DiscoveryKernel("App\\", SRC_DIR);

$scanner = $discoveryKernel->facade();

// 2. Registre o controller no container (ou permita autowiring via reflection)
$container->bind(FakeController::class, function () {
    return new FakeController();
});

// 3. Instancie a ControllerFactory com o container real
$factory = new ControllerFactory($container);

// 4. Instancie o kernel, injetando a factory real
$kernel = new RoutingKernel($scanner, $container);

// 5. Registre o provider real
$kernel->registerProviders(new RouteProviderDecorator());

// 6. Boot do kernel (carrega rotas, resolve providers, inicializa engine)
$kernel->boot();

// 7. Crie a requisição (request real)
$request = new ServerRequest(
    new HttpMethod('GET'),
    new RoutePath('/hello'),
    'localhost',
    'http'
);

// 8. Execute o dispatch e exiba o resultado
$result = $kernel->engine()->handle($request);

echo $result; // Esperado: "Hello, Routing!"
