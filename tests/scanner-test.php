<?php

require_once __DIR__ . '/test-bootstrap.php';

use App\Kernel\Discovery\DiscoveryKernel;
use App\Shared\Discovery\Application\Service\DiscoveryScanner;
use App\Shared\Discovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use App\Shared\Discovery\Infrastructure\FileToFqcnResolverPsr4;
use App\Shared\Discovery\Infrastructure\PhpFileFinderRecursive;
use App\Shared\Discovery\Domain\ValueObjects\InterfaceName;
use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;

/**
 * Prints a formatted status message.
 */
function printStatus(string $message, string $status = 'INFO'): void
{
    echo sprintf("[%s] %s%s", strtoupper($status), $message, PHP_EOL);
}

/**
 * Recursively deletes a directory and all its contents.
 */
function cleanup(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = "$dir/$item";
        if (is_dir($path)) {
            cleanup($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

/**
 * Registers a minimal PSR-4 autoloader for the test namespace.
 */
function registerAutoloader(string $namespacePrefix, string $psr4Dir): void
{
    spl_autoload_register(function ($class) use ($namespacePrefix, $psr4Dir) {
        $prefix = $namespacePrefix . '\\';
        $len = strlen($prefix);
        if (strncmp($class, $prefix, $len) !== 0) {
            return;
        }
        $relative = substr($class, $len);
        $file = $psr4Dir . '/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
}

/**
 * Cria diretório somente se não existir.
 */
function ensureDir(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

/**
 * Escreve arquivos de teste para o discovery sem kernel.
 */
function writeBasicTestFiles(string $psr4Dir, string $namespacePrefix): void
{
    ensureDir("{$psr4Dir}/Foo/Sub");

    file_put_contents("{$psr4Dir}/Foo/DummyInterface.php", <<<PHP
        <?php
        namespace {$namespacePrefix}\Foo;
        interface DummyInterface {}
        PHP
    );
    file_put_contents("{$psr4Dir}/Foo/ValidClass.php", <<<PHP
        <?php
        namespace {$namespacePrefix}\Foo;
        class ValidClass implements DummyInterface {}
        PHP
    );
    file_put_contents("{$psr4Dir}/Foo/InvalidClass.php", <<<PHP
        <?php
        namespace {$namespacePrefix}\Foo;
        class InvalidClass {}
        PHP
    );
    file_put_contents("{$psr4Dir}/Foo/Sub/SubValidClass.php", <<<PHP
        <?php
        namespace {$namespacePrefix}\Foo\Sub;
        use {$namespacePrefix}\Foo\DummyInterface;
        class SubValidClass implements DummyInterface {}
        PHP
    );
}

/**
 * Escreve arquivos de teste para o discovery com kernel.
 */
function writeKernelTestFiles(string $psr4Dir, string $namespacePrefix): void
{
    ensureDir("{$psr4Dir}/Extensions/Domain");
    ensureDir("{$psr4Dir}/Extensions/Other");

    file_put_contents("{$psr4Dir}/Extensions/ExtensionInterface.php", <<<PHP
        <?php
        namespace {$namespacePrefix}\Extensions;
        interface ExtensionInterface {}
        PHP
    );
    file_put_contents("{$psr4Dir}/Extensions/FooExtension.php", <<<PHP
        <?php
        namespace {$namespacePrefix}\Extensions;
        class FooExtension implements ExtensionInterface {}
        PHP
    );
    file_put_contents("{$psr4Dir}/Extensions/Domain/DomainExtension.php", <<<PHP
        <?php
        namespace {$namespacePrefix}\Extensions\Domain;
        use {$namespacePrefix}\Extensions\ExtensionInterface;
        class DomainExtension implements ExtensionInterface {}
        PHP
    );
    file_put_contents("{$psr4Dir}/Extensions/Other/Ignored.php", <<<PHP
        <?php
        namespace {$namespacePrefix}\Extensions\Other;
        class Ignored {}
        PHP
    );
}

/**
 * Gera ambiente isolado para cada cenário de teste.
 */
function createTestEnvironment(string $suffix): array
{
    $testId = uniqid($suffix . '_');
    $baseDir = sys_get_temp_dir() . "/DiscoveryTest_{$testId}";
    $namespacePrefix = "TempTestNS{$testId}";
    $psr4Dir = "{$baseDir}/src";
    return [
        'testId' => $testId,
        'baseDir' => $baseDir,
        'namespacePrefix' => $namespacePrefix,
        'psr4Dir' => $psr4Dir,
    ];
}

echo "<pre>";

// (1) TESTE FUNCIONAL DISCOVERY SEM KERNEL
printStatus("Discovery test: sem Kernel");

$config = createTestEnvironment('no_kernel');
ensureDir($config['psr4Dir'] . '/Foo/Sub');
writeBasicTestFiles($config['psr4Dir'], $config['namespacePrefix']);
registerAutoloader($config['namespacePrefix'], $config['psr4Dir']);

$resolver = new NamespaceToDirectoryResolverPsr4($config['namespacePrefix'], $config['psr4Dir']);
$fqcnResolver = new FileToFqcnResolverPsr4();
$finder = new PhpFileFinderRecursive();
$scanner = new DiscoveryScanner($resolver, $fqcnResolver, $finder);

$interface = new InterfaceName("{$config['namespacePrefix']}\\Foo\\DummyInterface");
$namespace = new NamespaceName("{$config['namespacePrefix']}\\Foo");

$result = $scanner->discoverImplementing($interface, $namespace);

$expected = [
    "{$config['namespacePrefix']}\\Foo\\ValidClass",
    "{$config['namespacePrefix']}\\Foo\\Sub\\SubValidClass"
];
$found = [];
foreach ($result as $fqcnObj) {
    $found[] = $fqcnObj->value();
    printStatus("Found: " . $fqcnObj->value());
}
sort($expected);
sort($found);

if ($found === $expected) {
    printStatus("Discovery sem Kernel OK", "OK");
} else {
    printStatus("Discovery sem Kernel FALHOU", "FAIL");
    printStatus("Esperado: " . implode(', ', $expected), "FAIL");
    printStatus("Encontrado: " . implode(', ', $found), "FAIL");
}

cleanup($config['baseDir']);

// (2) TESTE FUNCIONAL DISCOVERY COM KERNEL
printStatus("Discovery test: com Kernel");

$kconfig = createTestEnvironment('with_kernel');
ensureDir($kconfig['psr4Dir'] . '/Extensions/Domain');
ensureDir($kconfig['psr4Dir'] . '/Extensions/Other');
writeKernelTestFiles($kconfig['psr4Dir'], $kconfig['namespacePrefix']);
registerAutoloader($kconfig['namespacePrefix'], $kconfig['psr4Dir']);

$kernel = new DiscoveryKernel($kconfig['namespacePrefix'], $kconfig['psr4Dir']);

// a) Descoberta padrão
$foundDefault = [];
$default = $kernel->discoverExtensions();
foreach ($default as $fqcnObj) {
    $foundDefault[] = $fqcnObj->value();
    printStatus("Default: " . $fqcnObj->value());
}
$expectedDefault = [
    $kconfig['namespacePrefix'] . '\\Extensions\\FooExtension',
    $kconfig['namespacePrefix'] . '\\Extensions\\Domain\\DomainExtension'
];
sort($foundDefault);
sort($expectedDefault);
if ($foundDefault === $expectedDefault) {
    printStatus("Kernel default discovery OK", "OK");
} else {
    printStatus("Kernel default discovery FALHOU", "FAIL");
}

// b) Descoberta subnamespace direcionado
$foundDomain = [];
$domain = $kernel->discoverExtensions($kconfig['namespacePrefix'] . '\\Extensions\\Domain');
foreach ($domain as $fqcnObj) {
    $foundDomain[] = $fqcnObj->value();
    printStatus("Domain: " . $fqcnObj->value());
}
$expectedDomain = [
    $kconfig['namespacePrefix'] . '\\Extensions\\Domain\\DomainExtension'
];
sort($foundDomain);
if ($foundDomain === $expectedDomain) {
    printStatus("Kernel domain discovery OK", "OK");
} else {
    printStatus("Kernel domain discovery FALHOU", "FAIL");
}

// c) Descoberta em namespace inexistente (resultado vazio)
$empty = $kernel->discoverExtensions($kconfig['namespacePrefix'] . '\\Extensions\\Nonexistent');
if ($empty->isEmpty()) {
    printStatus("Kernel empty discovery OK", "OK");
} else {
    printStatus("Kernel empty discovery FALHOU", "FAIL");
}

// d) Descoberta com fallback (não acha em Nonexistent, mas acha no padrão)
$foundFallback = [];
$fallback = $kernel->discoverExtensions($kconfig['namespacePrefix'] . '\\Extensions\\Nonexistent', true);
foreach ($fallback as $fqcnObj) {
    $foundFallback[] = $fqcnObj->value();
    printStatus("Fallback: " . $fqcnObj->value());
}
sort($foundFallback);
if ($foundFallback === $expectedDefault) {
    printStatus("Kernel fallback discovery OK", "OK");
} else {
    printStatus("Kernel fallback discovery FALHOU", "FAIL");
}

cleanup($kconfig['baseDir']);
printStatus("Arquivos de teste limpos.", "OK");
printStatus("Todos os testes concluídos.", "INFO");
