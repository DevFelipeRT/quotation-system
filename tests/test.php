<?php declare(strict_types=1);

use App\Kernel\Adapters\EventListening\Contracts\EventBindingProviderInterface;
use App\Presentation\Http\Controllers\AbstractController;
use Discovery\Domain\ValueObjects\FullyQualifiedClassName;
use Discovery\Domain\ValueObjects\InterfaceName;
use Discovery\Domain\ValueObjects\NamespaceName;
use Discovery\Infrastructure\DiscoveryKernel;

require_once __DIR__ . '/test-bootstrap.php';
require_once BASE_PATH . '/autoload.php';
echo "<pre>";

$psr4Prefix = 'App\\';
$baseSourceDir = __DIR__ . '/../src';

function errorHandler($e)
{
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo 'Code: ' . $e->getCode() . PHP_EOL;
    echo 'File: ' . $e->getFile() . PHP_EOL;
    echo 'Line: ' . $e->getLine() . PHP_EOL;
    echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
    if ($e->getPrevious()) {
        echo 'Previous: ' . $e->getPrevious()->getMessage() . PHP_EOL;
    }
}

$discoveryKernel = new DiscoveryKernel(
    $psr4Prefix,
    $baseSourceDir
);

$scanner = $discoveryKernel->scanner();
var_dump($discoveryKernel);

try {
    $interfaceName = new InterfaceName(EventBindingProviderInterface::class);
    echo ' interfaceName success' . PHP_EOL;
    echo $interfaceName->value() . PHP_EOL;

    $namespace = new NamespaceName('App');
    echo ' namespace success' . PHP_EOL;
    echo $namespace->value() . PHP_EOL;

    $collection = $scanner->discoverImplementing(
        $interfaceName,
        $namespace
    );
} catch (\Throwable $e) {
    errorHandler($e);
}

$fqncCollection = $collection->toArray();

echo PHP_EOL;

echo 'Scanner results:' . PHP_EOL;
foreach ($fqncCollection as $fqcn) {
    echo 'FQCN: ';
    echo $fqcn->value() . PHP_EOL;
}
echo PHP_EOL;

try {
    $fqnc = new FullyQualifiedClassName(AbstractController::class);
    $extensions = $scanner->discoverExtending($fqnc);
} catch (\Throwable $e) {
    errorHandler($e);
}

$extensionsArray = $extensions->toArray();

echo 'Extensions scanning results:' . PHP_EOL;
foreach ($extensionsArray as $extension) {
    echo 'Extension FQCN: ';
    echo $extension->value() . PHP_EOL;
}
echo PHP_EOL;