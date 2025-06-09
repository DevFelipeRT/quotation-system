<?php 

declare(strict_types=1);

namespace Tests\Container;

require __DIR__ . '/../test-bootstrap.php';

use Container\Infrastructure\ContainerBuilder;
use Container\Infrastructure\Exceptions\CircularDependencyException;
use PublicContracts\Container\ContainerInterface;
use PublicContracts\Container\ServiceProviderInterface;
use Tests\IntegrationTestHelper;

class ContainerTest1 extends IntegrationTestHelper
{
    public ?ContainerBuilder $builder = null;
    public ?array $testConfig = null;
    public ?ContainerInterface $container = null;
    public ?array $bindings = null;

    public function __construct(?array $testConfig = null)
    {
        parent::__construct('Container Test');
        $this->testConfig = $testConfig;
    }

    public function run(): void
    {
        $this->setUp();
        $this->runInitializationTest();
        $this->runResolutionTests();
        $this->runAutowiringTests();
        $this->finalResult();
    }

    // === SETUP AND INITIALIZATION TESTS ===

    public function runInitializationTest(): void
    {
        $this->printStatus("Running initialization tests.", 'RUN');

        $this->printStatus("ContainerBuilder method.", 'TEST 1');
        $this->containerBuilder();
        $this->configureBuilder();
        $this->buildContainer();
        $this->printStatus("ContainerBuilder method completed.", 'OK');

        $this->printStatus("Initialization tests completed.", 'END');
    }

    /**
     * Initializes the ContainerBuilder instance.
     * This method attempts to create a new ContainerBuilder and handles any exceptions that may occur.
     * If successful, it sets the builder property; otherwise, it sets it to null.
     *
     * @return void
     */
    private function containerBuilder(): void
    {
        $this->printStatus("Initializing ContainerBuilder.", 'STEP', '1');
        try {
            $this->builder = new ContainerBuilder();
            $this->printStatus("ContainerBuilder initialized.", 'OK');
            $this->saveResult('ContainerBuilder initialization', true);
        } catch (\Throwable $e) {
            $this->printStatus("ContainerBuilder initialization failed.", 'ERROR');
            $this->saveResult('ContainerBuilder initialization', false);
            $this->handleException($e);
            $this->builder = null;
        }
    }

    private function configureBuilder(): void
    {
        if ($this->testConfig === null) {
            $this->printStatus("Builder configuration is not set. Cannot configure.", 'INFO');
            return;
        }

        $this->printStatus("Configuring ContainerBuilder.", 'STEP', '2');
        try {
            $this->configureBuilderBinding();
            $this->configureBuilderSingleton();
            $this->configureBuilderProvider();

            $this->printStatus("ContainerBuilder configured successfully.", 'OK');
            $this->saveResult('ContainerBuilder configuration', true);
        } catch (\Throwable $e) {
            $this->printStatus("ContainerBuilder configuration failed.", 'ERROR');
            $this->saveResult('ContainerBuilder configuration', false);
            $this->handleException($e);
        }
    }

    private function configureBuilderBinding(): void
    {
        $this->printStatus("Manually configuring trasient binding.", 'INFO');
            try {
                $bindingId = $this->testConfig['binding']['id'] ?? null;
                $bindingCallable = $this->testConfig['binding']['callable'] ?? null;
                $this->builder->bind($bindingId, $bindingCallable);

                $this->printStatus("Transient binding '$bindingId' configured successfully.", 'OK');
                $this->saveResult('Transient binding configuration', true);
            } catch (\Throwable $e) {
                $this->printStatus("Transient binding configuration failed.", 'ERROR');
                $this->saveResult('Transient binding configuration', false);
                $this->handleException($e);
            }
    }

    private function configureBuilderSingleton(): void
    {
        $this->printStatus("Configuring singleton.", 'INFO');
        try {
            $sindletonId = $this->testConfig['singleton']['id'] ?? null;
            $singletonCallable = $this->testConfig['singleton']['callable'] ?? null;
            $this->builder->singleton($sindletonId, $singletonCallable);

            $this->printStatus("Singleton '$sindletonId' configured successfully.", 'OK');
            $this->saveResult('Singleton configuration', true);
        } catch (\Throwable $e) {
            $this->printStatus("Singleton configuration failed.", 'ERROR');
            $this->saveResult('Singleton configuration', false);
            $this->handleException($e);
        }
    }

    private function configureBuilderProvider(): void
    {
        $this->printStatus("Adding provider.", 'INFO');
        try {
            if (!isset($this->testConfig['provider'])) {
                $this->printStatus("No provider configuration found. Skipping provider addition.", 'INFO');
                return;
            }
            $providerClass = $this->testConfig['provider'];
            $this->builder->addProvider($providerClass);

            $this->printStatus("Provider added successfully.", 'OK');
            $this->saveResult('Provider addition', true);
        } catch (\Throwable $e) {
            $this->printStatus("Provider addition failed.", 'ERROR');
            $this->saveResult('Provider addition', false);
            $this->handleException($e);
        }
    }

    private function buildContainer(): void
    {
        $this->printStatus("Building container.", 'STEP', '3');
        try {
            $this->container = $this->builder->build();

            $this->printStatus("Container built successfully.", 'OK');
            $this->saveResult('Container build with default config', true);
        } catch (\Throwable $e) {
            $this->printStatus("Container build failed.", 'ERROR');
            $this->saveResult('Container build with default config', false);
            $this->handleException($e);
        }
    }

    // === RESOLUTION TESTS ===

    private function runResolutionTests(): void
    {
        $this->printStatus("Running resolution tests.", 'RUN');
        
        if (!($this->container instanceof ContainerInterface)) {
            $this->printStatus("Skipping test: Container not available.", "WARN");
            $this->saveResult('Resolution tests', false);
            return;
        }

        $this->printStatus("Resolution tests.", 'TEST 1');
        $this->testBindingResolution();
        $this->testSingletonResolution();
        $this->testProviderResolution();
        $this->testCycleDependencyResolution();

        $this->printStatus("Resolution tests completed.", 'END');
    }

    private function testBindingResolution(): void
    {
        $this->printStatus("Testing transient binding resolution.", 'STEP', '1');
        if (!isset($this->testConfig['binding']['id'])) {
            $this->printStatus("No transient binding configuration found. Skipping test.", 'INFO');
            return;
        }

        $id = $this->testConfig['binding']['id'];
        try {
            $foo1 = $this->container->get($id);
            $foo2 = $this->container->get($id);
            $this->printStatus("Method \"get($id)\" called twice successfully.", 'OK');

            if ($foo1 !== $foo2) {
                $this->printStatus("Transient binding resolution returns different instances.", 'OK');
            } else {
                $this->printStatus("Transient binding resolution returns the same instance.", 'ERROR');
            }
        
            $this->saveResult("Transient binding resolution ($id) returns different instances", $foo1 !== $foo2);
        } catch (\Throwable $e) {
            $this->printStatus("Transient binding resolution failed.", 'ERROR');
            $this->saveResult('Transient binding resolution', false);
            $this->handleException($e);
        }
    }

    private function testSingletonResolution(): void
    {
        $this->printStatus("Testing singleton binding resolution.", 'STEP', '2');
        if (!isset($this->testConfig['singleton']['id'])) {
            $this->printStatus("No singleton configuration found. Skipping test.", 'INFO');
            return;
        }

        $id = $this->testConfig['singleton']['id'];
        try {
            $bar1 = $this->container->get($id);
            $bar2 = $this->container->get($id);
            $this->printStatus("Method \"get($id)\" called twice successfully.", 'OK');
            $this->saveResult("Singleton binding resolution ($id) returns same instance", $bar1 === $bar2);
        } catch (\Throwable $e) {
            $this->printStatus("Singleton binding ($id) resolution failed.", 'ERROR');
            $this->saveResult('Singleton binding resolution', false);
            $this->handleException($e);
        }
    }

    private function testProviderResolution(): void
    {
        $this->printStatus("Testing provider binding resolution.", 'STEP', '3');
        if (!isset($this->testConfig['provider'])) {
            $this->printStatus("No provider configuration found. Skipping test.", 'INFO');
            return;
        }

        try {
            $id = $this->testConfig['provider']->id;
            $class = $this->testConfig['provider']->class;
            $foo = $this->container->get($id);
            
            if ($foo instanceof $class) {
                $this->printStatus("Provider binding resolved successfully.", 'OK');
                $this->saveResult('Provider binding resolution', true);
            } else {
                $this->printStatus("Provider binding did not return expected type.", 'ERROR');
                $this->saveResult('Provider binding resolution', false);
            }
        } catch (\Throwable $e) {
            $this->printStatus("Provider binding resolution failed.", 'ERROR');
            $this->saveResult('Provider binding resolution', false);
            $this->handleException($e);
        }
    }

    private function testCycleDependencyResolution(): void
    {
        $this->printStatus("Testing cycle dependency resolution.", 'STEP', '4');
        if (!isset($this->testConfig['cycleClass'])) {
            $this->printStatus("No cycle dependency class configured. Skipping test.", 'INFO');
            return;
        }
        
        try {
            $class = $this->testConfig['cycleClass'];
            $this->container->get($class);
            $this->printStatus("Method get($class) executed (unexpectedly, should have thrown for cycle).', 'WARN_DETAIL");
            $this->saveResult("Circular dependency ($class) throws CircularDependencyException", false);
        } catch (CircularDependencyException $cde) {
            $this->printStatus("Method get($class) correctly threw CircularDependencyException.", 'OK');
            $this->saveResult("Circular dependency ($class) throws CircularDependencyException", true);
        } catch (\Throwable $e) {
            $this->printStatus("Method get($class) threw an unexpected exception during cycle test.", 'FAIL_DETAIL');
            $this->saveResult("Circular dependency ($class) throws CircularDependencyException", false);
            $this->handleException($e);
        }
    }

    // === AUTOWIRING TESTS ===

    public function runAutowiringTests(): void
    {
        $this->printStatus("Running autowiring resolution tests.", 'RUN');
        
        if (!($this->container instanceof ContainerInterface)) {
            $this->printStatus("Skipping test: Container not available.", "WARN");
            $this->saveResult('Autowiring resolution tests', false);
            return;
        }

        if (!isset($this->testConfig['autowire'])) {
            $this->printStatus("No autowiring configuration found. Skipping autowiring resolution tests.", 'INFO');
            return;
        }

        $this->printStatus("Autowiring resolution test.", 'TEST 1');
        $this->testSingleDependencyAutowiring();
        $this->testMultipleDependencyAutowiring();
        $this->testAutowiringCycleDependency();
        $this->printStatus("Autowiring resolution tests completed.", 'END');
    }

    public function testSingleDependencyAutowiring(): void
    {
        $this->printStatus("Testing single dependency autowiring resolution.", 'STEP', '1');
        if (!isset($this->testConfig['autowire']['singleDependency'])) {
            $this->printStatus("No single dependency autowiring configuration found. Skipping test.", 'INFO');
            return;
        }
        
        $class = $this->testConfig['autowire']['singleDependency'];
        try {
            $obj = $this->container->get($class);
            if ($obj instanceof $class) {
                $this->printStatus("Autowiring resolved \"$class\" successfully.", 'OK');
                $this->saveResult('Autowiring resolution', true);
            } else {
                $this->printStatus("Autowiring did not resolve \"$class\" correctly.", 'ERROR');
                $this->saveResult('Autowiring single dependency resolution', false);
            }
        } catch (\Throwable $e) {
            $this->printStatus("Autowiring single dependency resolution failed.", 'ERROR');
            $this->saveResult('Autowiring single dependency resolution', false);
            $this->handleException($e);
        }
    }

    public function testMultipleDependencyAutowiring(): void
    {
        $this->printStatus("Testing multiple dependency autowiring resolution.", 'STEP', '2');
        if (!isset($this->testConfig['autowire']['multipleDependency'])) {
            $this->printStatus("No multiple dependency autowiring configuration found. Skipping test.", 'INFO');
            return;
        }
        
        $class = $this->testConfig['autowire']['multipleDependency'];
        try {
            $obj = $this->container->get($class);
            if ($obj instanceof $class) {
                $this->printStatus("Autowiring resolved \"$class\" successfully.", 'OK');
                $this->saveResult('Autowiring multiple dependency resolution', true);
            } else {
                $this->printStatus("Autowiring did not resolve \"$class\" correctly.", 'ERROR');
                $this->saveResult('Autowiring multiple dependency resolution', false);
            }
        } catch (\Throwable $e) {
            $this->printStatus("Autowiring multiple dependency resolution failed.", 'ERROR');
            $this->saveResult('Autowiring multiple dependency resolution', false);
            $this->handleException($e);
        }
    }

    public function testAutowiringCycleDependency(): void
    {
        $this->printStatus("Testing autowiring cycle dependency resolution.", 'STEP', '3');
        if (!isset($this->testConfig['autowire']['cycle'])) {
            $this->printStatus("No cycle dependency autowiring configuration found. Skipping test.", 'INFO');
            return;
        }
        
        $class = $this->testConfig['autowire']['cycle'];
        try {
            $this->container->get($class);
            $this->printStatus("Method get($class) executed (unexpectedly, should have thrown for cycle).", 'WARN_DETAIL');
            $this->saveResult("Autowiring cycle dependency ($class) throws CircularDependencyException", false);
        } catch (CircularDependencyException $cde) {
            $this->printStatus("Method get($class) correctly threw CircularDependencyException.", 'OK');
            $this->saveResult("Autowiring cycle dependency ($class) throws CircularDependencyException", true);
        } catch (\Throwable $e) {
            $this->printStatus("Method get($class) threw an unexpected exception during cycle test.", 'FAIL_DETAIL');
            $this->saveResult("Autowiring cycle dependency ($class) throws CircularDependencyException", false);
            $this->handleException($e);
        }
    }
}

// === TEST CLASSES ===
class Foo {}
class Bar { public function __construct(public Foo $foo) {} }
class Baz { public function __construct(public Bar $bar, public Foo $foo) {} }
class CycleA { public function __construct(public CycleB $b) {} }
class CycleB { public function __construct(public CycleA $a) {} }

// === PROVIDER EXAMPLE ===
class TestProvider implements ServiceProviderInterface
{
    public string $id = 'provider_foo';
    public string $class = Foo::class;
    
    public function register(ContainerInterface $container): void
    {
        $container->bind($this->id, fn() => new Foo());
    }
}

// === CONTAINER BUILDER CONFIGURATION ===
$testConfig = [
    'binding' => [
        'id' => 'foo',
        'callable' => fn() => new Foo()
    ],
    'singleton' => [
        'id' => 'singleton_bar',
        'callable' => fn() => new Bar(new Foo())
    ],
    'provider' => new TestProvider(),
    'cycleClass' => CycleA::class,
    'autowire' => [
        'singleDependency' => Bar::class,
        'multipleDependency' => Baz::class,
        'cycle' => CycleB::class
    ]
];

$containerTest = new ContainerTest1($testConfig);
$containerTest->run();