<?php

declare(strict_types=1);

use Container\ContainerBuilder;
use Container\Bindings\BindingType;
use Container\Scope\TransientScope;
use PublicContracts\Container\ContainerInterface;
use PublicContracts\Container\ServiceProviderInterface;
use Container\Exceptions\CircularDependencyException;
use Container\Exceptions\NotFoundException;

// Test setup
echo "<pre>";
/** @var array<int, array{description: string, result: bool}> $testResult Collection of test results. */
$testResult = [];
/** @var ?ContainerInterface $container The main container instance used across tests. */
$container = null;
/** @var ?ContainerBuilder $builder The main container builder instance. */
$builder = null;
/** @var bool $canProceedCurrentStep Flag to control execution of current step's main logic. */
$canProceedCurrentStep = true;

// Helper functions

/**
 * Prints a formatted status message.
 * If a step number is provided and the status is 'STEP', it formats as [STEP X].
 *
 * @param string $message The message to print.
 * @param string $status The status type (e.g., INFO, STEP, OK, ERROR).
 * @param ?int $stepNumber Optional step number, used if status is 'STEP'.
 * @return void
 */
function printStatus(string $message, string $status = 'INFO', ?string $stepNumber = null): void
{
    $statusTag = strtoupper($status);
    // Only prepend step number if status is 'STEP' and a number is provided
    if ($stepNumber !== null && $status === 'STEP') {
        $statusOutput = sprintf("%s %s", $statusTag, $stepNumber);
    } else {
        $statusOutput = $statusTag;
    }
    echo sprintf("[%s] %s%s", $statusOutput, $message, PHP_EOL);
}

/**
 * Prints an immediate test result message.
 * This is often used for direct feedback, while saveResult() is used for the final summary.
 *
 * @param string $description Description of the check.
 * @param bool $result The outcome (true for OK, false for FAIL).
 * @return void
 */
function printResult(string $description, bool $result): void
{
    echo str_pad($description, 80, '.') . ($result ? "OK\n" : "FAIL\n");
}

/**
 * Prints a summary of all accumulated test results.
 *
 * @param array<int, array{description: string, result: bool}> $results The array of test results.
 * @return void
 */
function printAll(array $results): void
{
    echo PHP_EOL . "--- TEST SUMMARY ---" . PHP_EOL;
    foreach ($results as $result) {
        // Uses the printResult function for consistent formatting.
        printResult($result['description'], $result['result']);
    }
}

/**
 * Saves a test result to the global collection for the final summary.
 *
 * @param string $description Description of the test.
 * @param bool $result The outcome of the test (true for OK, false for FAIL).
 * @return void
 */
function saveResult(string $description, bool $result): void
{
    global $testResult;
    $testResult[] = ['description' => $description, 'result' => $result];
}

/**
 * Handles and prints details of an exception that occurred during test execution.
 *
 * @param \Throwable $e The caught exception/error.
 * @return void
 */
function handleException(\Throwable $e): void
{
    printStatus("Exception occurred: " . get_class($e), 'ERROR_DETAIL');
    echo "    [Message] {$e->getMessage()}\n";
    echo "    [File] {$e->getFile()}:{$e->getLine()}\n";
    // For more detailed debugging, the following trace can be uncommented.
    // echo "    [Trace] {$e->getTraceAsString()}\n";
}

// === BOOTSTRAP ===
printStatus("Starting container test.", 'INFO');
try {
    require __DIR__ . '/test-bootstrap.php';
    printStatus("Bootstrap executed successfully.", 'OK');
    saveResult('Bootstrap execution', true);
} catch (\Throwable $e) {
    saveResult('Bootstrap execution', false);
    handleException($e);
    printStatus("Bootstrap failed. Subsequent tests might be affected or fail.", 'ERROR');
}

// === TEST CLASSES ===
class Foo {}
class Bar { public function __construct(public Foo $foo) {} }
class Baz { public function __construct(public Bar $bar, public Foo $foo) {} }
class CycleA { public function __construct(public CycleB $b) {} }
class CycleB { public function __construct(public CycleA $a) {} }


// === PROVIDER EXAMPLE ===
/**
 * Test service provider for demonstration purposes.
 *
 * This provider registers a service named 'provider_foo' that resolves to an instance of Foo.
 * It illustrates the container's service provider functionality.
 */
class TestProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->bind('provider_foo', fn() => new Foo());
    }
}

// === 1. Build container with default config ===
printStatus("Building container with default configuration.", 'STEP', '1');
$canProceedCurrentStep = true; // Initialize for the first part of step 1

printStatus("Initializing ContainerBuilder.", 'STEP', '1.1');
try {
    $builder = new ContainerBuilder(); // Script-scoped builder instance
    printStatus("ContainerBuilder initialized.", 'OK');
    saveResult('ContainerBuilder initialization', true);
} catch (\Throwable $e) {
    saveResult('ContainerBuilder initialization', false);
    handleException($e);
    $builder = null;
    $canProceedCurrentStep = false; // Prevent container build if builder init failed
}

// Second part of Step 1: Building the container, depends on builder
if ($canProceedCurrentStep) { // Check if builder initialization was successful
    printStatus("Building container.", 'STEP', '1.2');
    try {
        // $builder is guaranteed to be non-null here if $canProceedCurrentStep is true from above
        $container = $builder // Script-scoped container instance
            ->bind('foo', fn() => new Foo())
            ->singleton('singleton_bar', fn() => new Bar(new Foo()))
            ->addProvider(new TestProvider())
            ->build();
        printStatus("Container built successfully.", 'OK');
        saveResult('Container build with default config', true);
    } catch (\Throwable $e) {
        saveResult('Container build with default config', false);
        handleException($e);
        $container = null; // Ensure container is null if build failed
    }
} else {
    // This else corresponds to builder initialization failure
    printStatus("Skipping container build: ContainerBuilder initialization failed.", "WARN");
    saveResult('Container build with default config', false);
    $container = null;
}


// === 2. Test basic binding resolution (transient) ===
$canProceedCurrentStep = true; // Reset flag for the new step
printStatus("Testing transient binding resolution ('foo').", 'STEP', '2');

if (!($container instanceof ContainerInterface)) {
    printStatus("Skipping test: Container not available.", "WARN");
    saveResult('Transient binding ("foo") returns different instances', false);
    $canProceedCurrentStep = false;
}

if ($canProceedCurrentStep) {
    try {
        $foo1 = $container->get('foo');
        $foo2 = $container->get('foo');
        printStatus("get('foo') called twice successfully.", 'OK');
        saveResult('Transient binding ("foo") returns different instances', $foo1 !== $foo2);
    } catch (\Throwable $e) {
        saveResult('Transient binding ("foo") returns different instances', false);
        handleException($e);
    }
}

// === 3. Test singleton resolution ===
$canProceedCurrentStep = true; // Reset flag
printStatus("Testing singleton binding resolution ('singleton_bar').", 'STEP', '3');

if (!($container instanceof ContainerInterface)) {
    printStatus("Skipping test: Container not available.", "WARN");
    saveResult('Singleton binding ("singleton_bar") returns same instance', false);
    $canProceedCurrentStep = false;
}

if ($canProceedCurrentStep) {
    try {
        $bar1 = $container->get('singleton_bar');
        $bar2 = $container->get('singleton_bar');
        printStatus("get('singleton_bar') called twice successfully.", 'OK');
        saveResult('Singleton binding ("singleton_bar") returns same instance', $bar1 === $bar2);
    } catch (\Throwable $e) {
        saveResult('Singleton binding ("singleton_bar") returns same instance', false);
        handleException($e);
    }
}

// === 4. Test provider registration ===
$canProceedCurrentStep = true; // Reset flag
printStatus("Testing provider registration ('provider_foo').", 'STEP', '4');

if (!($container instanceof ContainerInterface)) {
    printStatus("Skipping test: Container not available.", "WARN");
    saveResult('Service provider binding ("provider_foo") is resolved to Foo', false);
    $canProceedCurrentStep = false;
}

if ($canProceedCurrentStep) {
    try {
        $providerFoo = $container->get('provider_foo');
        printStatus("get('provider_foo') successful.", 'OK');
        saveResult('Service provider binding ("provider_foo") is resolved to Foo', $providerFoo instanceof Foo);
    } catch (\Throwable $e) {
        saveResult('Service provider binding ("provider_foo") is resolved to Foo', false);
        handleException($e);
    }
}

// === 5. Test autowiring (class name not registered) ===
$canProceedCurrentStep = true; // Reset flag
printStatus("Testing autowiring (Baz::class with dependencies).", 'STEP', '5');

if (!($container instanceof ContainerInterface)) {
    printStatus("Skipping test: Container not available.", "WARN");
    saveResult('Autowiring Baz::class resolves with correct dependencies', false);
    $canProceedCurrentStep = false;
}

if ($canProceedCurrentStep) {
    try {
        $autoBaz = $container->get(Baz::class);
        printStatus('get(Baz::class) executed successfully.', 'OK');
        $ok = $autoBaz instanceof Baz && $autoBaz->bar instanceof Bar && $autoBaz->foo instanceof Foo;
        saveResult('Autowiring Baz::class resolves with correct dependencies', $ok);
        if (!$ok && ($autoBaz instanceof Baz)) {
            printStatus('   Dependency check for autowired Baz::class failed.', 'WARN_DETAIL');
            if (!($autoBaz->bar instanceof Bar)) printStatus('     Detail: autoBaz->bar is not an instance of Bar.', 'INFO');
            if (!($autoBaz->foo instanceof Foo)) printStatus('     Detail: autoBaz->foo is not an instance of Foo.', 'INFO');
        }
    } catch (\Throwable $e) {
        saveResult('Autowiring Baz::class resolves with correct dependencies', false);
        handleException($e);
    }
}

// === 6. Test has() behavior ===
$canProceedCurrentStep = true; // Reset flag
printStatus("Testing has() behavior.", 'STEP', '6');

if (!($container instanceof ContainerInterface)) {
    printStatus("Skipping test: Container not available.", "WARN");
    saveResult('Has() returns true for registered "foo"', false);
    saveResult('Has() returns false for autowirable but unbound Baz::class', false);
    saveResult('Has() returns false for "unknown_service"', false);
    $canProceedCurrentStep = false;
}

if ($canProceedCurrentStep) {
    try {
        $hasFoo = $container->has('foo');
        saveResult('Has() returns true for registered "foo"', $hasFoo);

        $hasBaz = $container->has(Baz::class);
        saveResult('Has() returns false for autowirable but unbound Baz::class', !$hasBaz);

        $hasUnknown = $container->has('unknown_service');
        saveResult('Has() returns false for "unknown_service"', !$hasUnknown);

        printStatus('has() checks completed.', 'OK');
    } catch (\Throwable $e) {
        saveResult('Has() returns true for registered "foo"', false); // Mark all as failed if has() itself throws
        saveResult('Has() returns false for autowirable but unbound Baz::class', false);
        saveResult('Has() returns false for "unknown_service"', false);
        printStatus('Exception during has() checks.', 'ERROR_DETAIL');
        handleException($e);
    }
}

// === 7. Test clearing bindings ===
$canProceedCurrentStep = true; // Reset flag
printStatus("Testing clearing binding 'singleton_bar' and checking resolution.", 'STEP', '7');

if (!($container instanceof ContainerInterface)) {
    printStatus("Skipping test: Container not available.", "WARN");
    saveResult('Cleared "singleton_bar" throws NotFoundException on get()', false);
    $canProceedCurrentStep = false;
}

if ($canProceedCurrentStep) {
    try {
        $container->clear('singleton_bar');
        printStatus('clear("singleton_bar") executed.', 'OK');
        try {
            $container->get('singleton_bar');
            printStatus('get("singleton_bar") after clear executed (unexpectedly).', 'WARN_DETAIL');
            saveResult('Cleared "singleton_bar" throws NotFoundException on get()', false);
        } catch (NotFoundException $nfe) {
            printStatus('get("singleton_bar") after clear correctly threw NotFoundException.', 'OK');
            saveResult('Cleared "singleton_bar" throws NotFoundException on get()', true);
        } catch (\Throwable $innerE) {
            printStatus('get("singleton_bar") after clear threw an unexpected exception.', 'FAIL_DETAIL');
            saveResult('Cleared "singleton_bar" throws NotFoundException on get()', false);
            handleException($innerE);
        }
    } catch (\Throwable $e) {
        saveResult('Cleared "singleton_bar" throws NotFoundException on get()', false);
        handleException($e);
    }
}

// === 8. Test cycle detection (CycleA -> CycleB -> CycleA) ===
$canProceedCurrentStep = true; // Reset flag
printStatus("Testing cycle detection (autowiring CycleA).", 'STEP', '8');

if (!($container instanceof ContainerInterface)) {
    printStatus("Skipping test: Container not available.", "WARN");
    saveResult('Circular dependency (CycleA) throws CircularDependencyException', false);
    $canProceedCurrentStep = false;
}

if ($canProceedCurrentStep) {
    try {
        $container->get(CycleA::class);
        printStatus('get(CycleA::class) executed (unexpectedly, should have thrown for cycle).', 'WARN_DETAIL');
        saveResult('Circular dependency (CycleA) throws CircularDependencyException', false);
    } catch (CircularDependencyException $cde) {
        printStatus('get(CycleA::class) correctly threw CircularDependencyException.', 'OK');
        saveResult('Circular dependency (CycleA) throws CircularDependencyException', true);
    } catch (\Throwable $e) {
        printStatus('get(CycleA::class) threw an unexpected exception during cycle test.', 'FAIL_DETAIL');
        saveResult('Circular dependency (CycleA) throws CircularDependencyException', false);
        handleException($e);
    }
}

// === 9. Test scopes: override singleton with transient ===
// This test creates its own container, so it doesn't depend on the global $container's state in the same way.
// No $canProceedCurrentStep check related to global $container needed here.
printStatus("Testing scope override (singleton to transient for 'foo_scoped').", 'STEP', '9');
try {
    $builder2 = new ContainerBuilder();
    $builder2->addScope(BindingType::SINGLETON, new TransientScope());
    $container2 = $builder2->singleton('foo_scoped', fn() => new Foo())->build();
    printStatus('New container with overridden scope built successfully.', 'OK');

    $fooA = $container2->get('foo_scoped');
    $fooB = $container2->get('foo_scoped');
    $isTransient = $fooA !== $fooB;
    saveResult('Overridden singleton "foo_scoped" behaves as transient (instances differ)', $isTransient);
    if (!$isTransient) {
        printStatus('   Instances of "foo_scoped" were identical, expected different.', 'WARN_DETAIL');
    } else {
        printStatus('   Instances of "foo_scoped" were different as expected.', 'INFO');
    }
} catch (\Throwable $e) {
    saveResult('Overridden singleton "foo_scoped" behaves as transient (instances differ)', false);
    handleException($e);
}

// === 10. Test reset() functionality on main container ===
$canProceedCurrentStep = true; // Reset flag
printStatus("Testing reset() on main container and autowiring 'foo'.", 'STEP', '10');

if (!($container instanceof ContainerInterface)) {
    printStatus("Skipping test: Container not available.", "WARN");
    saveResult('Reset: get("foo") autowires Foo after explicit binding cleared', false);
    saveResult('Reset: Direct autowiring of Foo::class works after reset', false); // Also mark sub-test as failed
    $canProceedCurrentStep = false;
}

if ($canProceedCurrentStep) {
    try {
        $container->reset();
        printStatus('Main container reset() method called.', 'OK');

        try {
            $fooAfterReset = $container->get('foo');
            $autowiringWorked = $fooAfterReset instanceof Foo;
            saveResult('Reset: get("foo") autowires Foo after explicit binding cleared', $autowiringWorked);
            if ($autowiringWorked) {
                printStatus('   get("foo") after reset successfully autowired Foo (as per original test logic).', 'INFO');
            } else {
                printStatus('   get("foo") after reset did NOT return Foo instance (original test expectation failed).', 'WARN_DETAIL');
            }
        } catch (NotFoundException $nfe) {
            saveResult('Reset: get("foo") autowires Foo after explicit binding cleared', false);
            printStatus('   get("foo") after reset threw NotFoundException.', 'WARN_DETAIL');
            printStatus('   (This means the string "foo" was not autowired to Foo::class after reset)', 'INFO');

            printStatus('   Checking direct autowiring of Foo::class post-reset.', 'SUB_STEP');
            try {
                $fooDirect = $container->get(Foo::class);
                $directAutowiringOk = $fooDirect instanceof Foo;
                saveResult('Reset: Direct autowiring of Foo::class works after reset', $directAutowiringOk);
                 if ($directAutowiringOk) {
                    printStatus('   Direct autowiring of Foo::class worked as expected.', 'OK');
                } else {
                    printStatus('   Direct autowiring of Foo::class did not return Foo instance.', 'FAIL_DETAIL');
                }
            } catch (\Throwable $eDirect) {
                saveResult('Reset: Direct autowiring of Foo::class works after reset', false);
                printStatus('   Direct autowiring of Foo::class threw an exception.', 'ERROR_DETAIL');
                handleException($eDirect);
            }
        } catch (\Throwable $innerE) {
            saveResult('Reset: get("foo") autowires Foo after explicit binding cleared', false);
            handleException($innerE);
        }
    } catch (\Throwable $e) {
        saveResult('Reset: get("foo") autowires Foo after explicit binding cleared', false);
        saveResult('Reset: Direct autowiring of Foo::class works after reset', false);
        handleException($e);
    }
}

echo "\nAll tests finished.\n";
printAll($testResult);
echo "</pre>";