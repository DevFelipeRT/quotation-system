<?php

declare(strict_types=1);

use App\Shared\Container\Domain\Contracts\ServiceProviderInterface;
use App\Shared\Container\Domain\Exceptions\CircularDependencyException;
use App\Shared\Container\Domain\Exceptions\NotFoundException;
use App\Shared\Container\Infrastructure\Bindings\BindingType;
use App\Shared\Container\Infrastructure\ContainerBuilder;
use App\Shared\Container\Infrastructure\Scope\TransientScope;

require_once __DIR__ . '/../autoload.php';

echo "<pre>";

// Helper for status output
function printResult(string $description, bool $result) {
    echo str_pad($description, 60, '.') . ($result ? "OK\n" : "FAIL\n");
}

// === TEST CLASSES ===
class Foo {}
class Bar { public function __construct(public Foo $foo) {} }
class Baz { public function __construct(public Bar $bar, public Foo $foo) {} }

// === PROVIDER EXAMPLE ===
class TestProvider implements ServiceProviderInterface {
    public function register(\App\Shared\Container\Domain\Contracts\ContainerInterface $container): void {
        $container->bind('provider_foo', fn() => new Foo());
    }
}

// === 1. Build container with default config ===
$builder = new ContainerBuilder();
$container = $builder
    ->bind('foo', fn() => new Foo())
    ->singleton('singleton_bar', fn() => new Bar(new Foo()))
    ->addProvider(new TestProvider())
    ->build();

// === 2. Test basic binding resolution (transient) ===
$foo1 = $container->get('foo');
$foo2 = $container->get('foo');
printResult('Transient binding returns different instances', $foo1 !== $foo2);

// === 3. Test singleton resolution ===
$bar1 = $container->get('singleton_bar');
$bar2 = $container->get('singleton_bar');
printResult('Singleton binding returns same instance', $bar1 === $bar2);

// === 4. Test provider registration ===
$providerFoo = $container->get('provider_foo');
printResult('Service provider binding is resolved', $providerFoo instanceof Foo);

// === 5. Test autowiring (class name not registered) ===
try {
    $autoBaz = $container->get(Baz::class);
    $ok = $autoBaz instanceof Baz && $autoBaz->bar instanceof Bar && $autoBaz->foo instanceof Foo;
    printResult('Autowiring resolves with dependencies', $ok);
} catch (\Throwable $e) {
    printResult('Autowiring resolves with dependencies', false);
    echo "  [Exception] {$e->getMessage()}\n";
}

// === 6. Test has() behavior ===
printResult('Has returns true for registered', $container->has('foo'));
printResult('Has returns false for autowirable class', !$container->has(Baz::class));
printResult('Has returns false for unknown', !$container->has('unknown_service'));

// === 7. Test clearing bindings ===
$container->clear('singleton_bar');
try {
    $container->get('singleton_bar');
    printResult('Cleared binding throws NotFoundException', false);
} catch (NotFoundException) {
    printResult('Cleared binding throws NotFoundException', true);
}

// === 8. Test cycle detection (should throw for circular dependencies) ===
class CycleA { public function __construct(CycleB $b) {} }
class CycleB { public function __construct(CycleA $a) {} }

try {
    $container->get(CycleA::class);
    printResult('Circular dependency throws CircularDependencyException', false);
} catch (CircularDependencyException) {
    printResult('Circular dependency throws CircularDependencyException', true);
}

// === 9. Test scopes: override singleton with transient ===
$builder2 = new ContainerBuilder();
$builder2->addScope(BindingType::SINGLETON, new TransientScope()); // Importante: escopo antes dos bindings!
$container2 = $builder2->singleton('foo', fn() => new Foo())->build();
$fooA = $container2->get('foo');
$fooB = $container2->get('foo');
printResult('Overridden singleton is now transient', $fooA !== $fooB);

// === 10. Test reset() ===
$container->reset();
try {
    // Autowiring para Foo deve funcionar, já que Foo é um FQCN válido.
    $foo = $container->get('foo');
    printResult('Reset clears all explicit bindings, autowiring still works', $foo instanceof Foo);
} catch (NotFoundException) {
    printResult('Reset clears all explicit bindings, autowiring still works', false);
}

echo "\nAll tests finished.\n";
