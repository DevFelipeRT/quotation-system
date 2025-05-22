<?php

declare(strict_types=1);

require_once __DIR__ . '/../autoload.php';

// Helper para resultado simples
function printResult(string $description, bool $result) {
    echo str_pad($description, 60, '.') . ($result ? "OK\n" : "FAIL\n");
}

// === TEST CLASSES ===
class Foo {}
class Bar { public function __construct(public Foo $foo) {} }
class Baz { public function __construct(public Bar $bar, public Foo $foo) {} }

// === Instancia container limpo (sem bindings, sem providers) ===
use App\Shared\Container\Infrastructure\Container;
$container = new Container(); // Nenhum binding explícito

// === Teste autowiring puro ===
try {
    $autoBaz = $container->get(Baz::class);
    $ok = $autoBaz instanceof Baz && $autoBaz->bar instanceof Bar && $autoBaz->foo instanceof Foo;
    printResult('Autowiring puro resolve Baz com dependências', $ok);
} catch (\Throwable $e) {
    printResult('Autowiring puro resolve Baz com dependências', false);
    echo "  [Exception] {$e->getMessage()}\n";
}

