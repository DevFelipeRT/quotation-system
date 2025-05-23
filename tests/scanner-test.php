<?php declare(strict_types=1);

/**
 * scanner-test.php
 *
 * Performs functional tests for class discovery mechanisms.
 * It uses isolated environments and a structured approach to test individual discovery assertions.
 */

require_once __DIR__ . '/test-bootstrap.php';

use App\Kernel\Discovery\DiscoveryKernel;
use App\Shared\Discovery\Application\Service\DiscoveryScanner;
use App\Shared\Discovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use App\Shared\Discovery\Infrastructure\FileToFqcnResolverPsr4;
use App\Shared\Discovery\Infrastructure\PhpFileFinderRecursive;
use App\Shared\Discovery\Domain\ValueObjects\InterfaceName;
use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;

echo "<pre>";

/** @var array<int, array{description: string, result: bool}> $testResult Collection of test results. */
$testResult = [];
/** @var bool $canProceed Flag to control execution if core bootstrap fails. */
$canProceed = true;

// --- Test Utility Functions (printStatus, printResult, printAll, saveResult, handleException) ---
// These remain largely the same as they are general utilities.
function printStatus(string $message, string $status = 'INFO', ?string $stepNumber = null): void
{
    $statusTag = strtoupper($status);
    if ($stepNumber !== null && $status === 'STEP') {
        $statusOutput = sprintf("%s %s", $statusTag, $stepNumber);
    } else {
        $statusOutput = $statusTag;
    }
    echo sprintf("[%s] %s%s", $statusOutput, $message, PHP_EOL);
}

function printResult(string $description, bool $result): void
{
    $maxLength = 68; 
    if (strlen($description) > $maxLength) {
        $description = substr($description, 0, $maxLength - 3) . '...';
    }
    echo str_pad($description, $maxLength + 2, '.') . ($result ? "OK\n" : "FAIL\n");
}

function printAll(array $results): void
{
    echo PHP_EOL . "--- TEST SUMMARY ---" . PHP_EOL;
    foreach ($results as $result) {
        printResult($result['description'], $result['result']);
    }
}

function saveResult(string $description, bool $result): void
{
    global $testResult;
    $testResult[] = ['description' => $description, 'result' => $result];
}

function handleExceptionInTest(\Throwable $e, string $testName): void 
{
    printStatus("Exception during test: " . $testName, 'ERROR_DETAIL');
    echo "    [Type] " . get_class($e) . "\n";
    echo "    [Message] {$e->getMessage()}\n";
    echo "    [File] {$e->getFile()}:{$e->getLine()}\n";
}


// --- Environment Setup/Teardown Functions (cleanup, registerAutoloader, ensureDir, etc.) ---
// These remain largely the same.
function cleanup(string $dir): void
{
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    if ($items === false) { printStatus("Failed to scan dir for cleanup: {$dir}", 'WARN_DETAIL'); return; }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) cleanup($path); else @unlink($path);
    }
    @rmdir($dir);
    printStatus("Cleaned up directory: {$dir}", 'DEBUG');
}

function registerAutoloader(string $namespacePrefix, string $psr4Dir): void
{
    spl_autoload_register(function ($class) use ($namespacePrefix, $psr4Dir) {
        $prefix = rtrim($namespacePrefix, '\\') . '\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
        $relativeClass = substr($class, strlen($prefix));
        $file = rtrim($psr4Dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
        if (file_exists($file)) require_once $file;
    }, true, true);
    printStatus("Autoloader registered for '{$namespacePrefix}' -> '{$psr4Dir}'.", 'DEBUG');
}

function ensureDir(string $dir): void
{
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" could not be created.', $dir));
        }
        printStatus("Ensured directory exists: {$dir}", 'DEBUG');
    }
}

function writeBasicTestFiles(string $psr4Dir, string $namespacePrefix): void 
{
    ensureDir("{$psr4Dir}/Foo/Sub");
    file_put_contents("{$psr4Dir}/Foo/DummyInterface.php", "<?php namespace {$namespacePrefix}\Foo; interface DummyInterface {}");
    file_put_contents("{$psr4Dir}/Foo/ValidClass.php", "<?php namespace {$namespacePrefix}\Foo; class ValidClass implements DummyInterface {}");
    file_put_contents("{$psr4Dir}/Foo/InvalidClass.php", "<?php namespace {$namespacePrefix}\Foo; class InvalidClass {}");
    file_put_contents("{$psr4Dir}/Foo/Sub/SubValidClass.php", "<?php namespace {$namespacePrefix}\Foo\Sub; use {$namespacePrefix}\Foo\DummyInterface; class SubValidClass implements DummyInterface {}");
    printStatus("Basic test files written for '{$namespacePrefix}'.", 'DEBUG');
}

function writeKernelTestFiles(string $psr4Dir, string $namespacePrefix): void
{
    ensureDir("{$psr4Dir}/Extensions/Domain"); ensureDir("{$psr4Dir}/Extensions/Other");
    file_put_contents("{$psr4Dir}/Extensions/ExtensionInterface.php", "<?php namespace {$namespacePrefix}\Extensions; interface ExtensionInterface {}");
    file_put_contents("{$psr4Dir}/Extensions/FooExtension.php", "<?php namespace {$namespacePrefix}\Extensions; class FooExtension implements ExtensionInterface {}");
    file_put_contents("{$psr4Dir}/Extensions/Domain/DomainExtension.php", "<?php namespace {$namespacePrefix}\Extensions\Domain; use {$namespacePrefix}\Extensions\ExtensionInterface; class DomainExtension implements ExtensionInterface {}");
    file_put_contents("{$psr4Dir}/Extensions/Other/Ignored.php", "<?php namespace {$namespacePrefix}\Extensions\Other; class Ignored {}");

    ensureDir("{$psr4Dir}/Services/Sub"); ensureDir("{$psr4Dir}/Services/NonExistent");
    file_put_contents("{$psr4Dir}/Services/GenericDiscoverableInterface.php", "<?php namespace {$namespacePrefix}\Services; interface GenericDiscoverableInterface {}");
    file_put_contents("{$psr4Dir}/Services/ServiceImpl1.php", "<?php namespace {$namespacePrefix}\Services; class ServiceImpl1 implements GenericDiscoverableInterface {}");
    file_put_contents("{$psr4Dir}/Services/Sub/ServiceImpl2.php", "<?php namespace {$namespacePrefix}\Services\Sub; use {$namespacePrefix}\Services\GenericDiscoverableInterface; class ServiceImpl2 implements GenericDiscoverableInterface {}");
    file_put_contents("{$psr4Dir}/Services/NonRelatedService.php", "<?php namespace {$namespacePrefix}\Services; class NonRelatedService {}");
    printStatus("Kernel test files (Ext & Services) written for '{$namespacePrefix}'.", 'DEBUG');
}

function createTestEnvironment(string $suffix): array 
{
    $testId = uniqid($suffix . '_');
    $baseDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "DiscoveryTest_{$testId}";
    $namespacePrefix = "TempTestNS{$testId}";
    $psr4Dir = $baseDir . DIRECTORY_SEPARATOR . 'src';
    ensureDir($psr4Dir);
    printStatus("Created test environment '{$testId}' at '{$baseDir}'.", 'INFO');
    return ['testId' => $testId, 'baseDir' => $baseDir, 'namespacePrefix' => $namespacePrefix, 'psr4Dir' => $psr4Dir];
}

// --- Test Runner ---

/**
 * Executes a single test case function and handles its reporting.
 *
 * @param string   $testSummaryDescription A concise description for the test summary.
 * @param callable $testFunction         The function containing the test logic. It should perform assertions and can throw exceptions.
 * @return void
 */
function runTest(string $testSummaryDescription, callable $testFunction): void
{
    printStatus("Executing: " . $testSummaryDescription, 'INFO');
    try {
        $testFunction(); // The test function is responsible for its specific assertions and saveResult calls.
    } catch (\Throwable $e) {
        // This catch block is for unexpected errors within the test function itself,
        // not for expected exceptions that the test might be asserting.
        saveResult($testSummaryDescription . " (Unexpected Exception)", false);
        handleExceptionInTest($e, $testSummaryDescription);
    }
}

// --- Test Definitions ---

// == Test Suite 1: Non-Kernel Generic Implementation Discovery ==

/**
 * Tests direct discovery of implementations using DiscoveryScanner.
 * @param array $envConfig Configuration for the test environment.
 */
function testNonKernel_FindsImplementationsInFoo(array $envConfig): void
{
    $interfaceFqcn = "{$envConfig['namespacePrefix']}\\Foo\\DummyInterface";
    $namespaceFqcn = "{$envConfig['namespacePrefix']}\\Foo";
    $summaryDesc = "Non-Kernel: Finds 'DummyInterface' in '{$namespaceFqcn}'";
    printStatus("Detail: Scanning '{$namespaceFqcn}' for '{$interfaceFqcn}'", 'DEBUG');

    $resolver = new NamespaceToDirectoryResolverPsr4($envConfig['namespacePrefix'], $envConfig['psr4Dir']);
    $fqcnResolver = new FileToFqcnResolverPsr4();
    $finder = new PhpFileFinderRecursive();
    $scanner = new DiscoveryScanner($resolver, $fqcnResolver, $finder);
    
    $interfaceVO = new InterfaceName($interfaceFqcn); 
    $namespaceVO = new NamespaceName($namespaceFqcn); 
    $results = $scanner->discoverImplementing($interfaceVO, $namespaceVO);

    $found = []; foreach ($results as $o) {$found[] = $o->value();} 
    $expected = [
        "{$envConfig['namespacePrefix']}\\Foo\\ValidClass",
        "{$envConfig['namespacePrefix']}\\Foo\\Sub\\SubValidClass"
    ];
    sort($found); sort($expected);

    if ($found === $expected) {
        printStatus("Assertion PASSED: Found expected implementations.", "OK_DETAIL");
        saveResult($summaryDesc, true);
    } else {
        printStatus("Assertion FAILED: Implementations do not match.", "FAIL_DETAIL");
        printStatus("Expected: ".implode(', ',$expected), "FAIL_DETAIL"); 
        printStatus("Found:    ".implode(', ',$found), "FAIL_DETAIL");
        saveResult($summaryDesc, false);
    }
}

// == Test Suite 2: Kernel-based Discovery ==

// --- Kernel Extension Discovery Tests ---

/**
 * Tests kernel's default extension discovery.
 * @param array $envConfig Test environment configuration.
 * @param DiscoveryKernel $kernel Instantiated DiscoveryKernel.
 */
function testKernelExtensions_DefaultDiscovery(array $envConfig, DiscoveryKernel $kernel): void
{
    $summaryDesc = "Kernel Ext: Default discovery finds all standard extensions";
    printStatus("Detail: Kernel default extension scan (ns=null, fallback=false)", 'DEBUG');
    
    $results = $kernel->discoverExtensions(); 
    $found = []; foreach ($results as $o) {$found[] = $o->value();}
    $expected = [
        $envConfig['namespacePrefix'].'\\Extensions\\FooExtension', 
        $envConfig['namespacePrefix'].'\\Extensions\\Domain\\DomainExtension'
    ];
    sort($found); sort($expected);

    if ($found === $expected) {
        printStatus("Assertion PASSED: Found expected default extensions.", "OK_DETAIL");
        saveResult($summaryDesc, true);
    } else {
        printStatus("Assertion FAILED: Default extensions do not match.", "FAIL_DETAIL");
        printStatus("Expected: ".implode(', ',$expected), "FAIL_DETAIL"); 
        printStatus("Found:    ".implode(', ',$found), "FAIL_DETAIL");
        saveResult($summaryDesc, false);
    }
}

/**
 * Tests kernel's targeted sub-namespace extension discovery.
 * @param array $envConfig Test environment configuration.
 * @param DiscoveryKernel $kernel Instantiated DiscoveryKernel.
 */
function testKernelExtensions_TargetedSubNamespace(array $envConfig, DiscoveryKernel $kernel): void
{
    $targetNs = $envConfig['namespacePrefix'] . '\\Extensions\\Domain';
    $summaryDesc = "Kernel Ext: Targeted discovery in '{$targetNs}'";
    printStatus("Detail: Kernel targeted extension scan for '{$targetNs}' (no fallback)", 'DEBUG');

    $results = $kernel->discoverExtensions($targetNs); 
    $found = []; foreach ($results as $o) {$found[] = $o->value();}
    $expected = [$envConfig['namespacePrefix'].'\\Extensions\\Domain\\DomainExtension'];
    sort($found); sort($expected);

    if ($found === $expected) {
        printStatus("Assertion PASSED: Found expected targeted extensions.", "OK_DETAIL");
        saveResult($summaryDesc, true);
    } else {
        printStatus("Assertion FAILED: Targeted extensions do not match.", "FAIL_DETAIL");
        printStatus("Expected: ".implode(', ',$expected), "FAIL_DETAIL"); 
        printStatus("Found:    ".implode(', ',$found), "FAIL_DETAIL");
        saveResult($summaryDesc, false);
    }
}

/**
 * Tests kernel's extension discovery in a non-existent namespace (expects empty).
 * @param array $envConfig Test environment configuration.
 * @param DiscoveryKernel $kernel Instantiated DiscoveryKernel.
 */
function testKernelExtensions_NonExistentNamespaceIsEmpty(array $envConfig, DiscoveryKernel $kernel): void
{
    $targetNs = $envConfig['namespacePrefix'] . '\\Extensions\\NonExistentExt';
    $summaryDesc = "Kernel Ext: Non-existent namespace '{$targetNs}' yields empty";
    printStatus("Detail: Kernel scan of non-existent '{$targetNs}' for extensions (expect empty)", 'DEBUG');

    $results = $kernel->discoverExtensions($targetNs); 
    $isEmpty = method_exists($results, 'isEmpty') ? $results->isEmpty() : empty((array)$results);

    if ($isEmpty) {
        printStatus("Assertion PASSED: Non-existent namespace correctly yields empty set.", "OK_DETAIL");
        saveResult($summaryDesc, true);
    } else {
        printStatus("Assertion FAILED: Non-existent namespace was not empty.", "FAIL_DETAIL");
        saveResult($summaryDesc, false);
    }
}

/**
 * Tests kernel's fallback extension discovery.
 * @param array $envConfig Test environment configuration.
 * @param DiscoveryKernel $kernel Instantiated DiscoveryKernel.
 */
function testKernelExtensions_FallbackDiscovery(array $envConfig, DiscoveryKernel $kernel): void
{
    $targetNs = $envConfig['namespacePrefix'] . '\\Extensions\\NonExistentForFallback';
    $summaryDesc = "Kernel Ext: Fallback from '{$targetNs}' finds default extensions";
    printStatus("Detail: Kernel fallback scan from '{$targetNs}' (fallback=true)", 'DEBUG');

    $results = $kernel->discoverExtensions($targetNs, true); 
    $found = []; foreach ($results as $o) {$found[] = $o->value();}
    $expected = [
        $envConfig['namespacePrefix'].'\\Extensions\\FooExtension', 
        $envConfig['namespacePrefix'].'\\Extensions\\Domain\\DomainExtension'
    ];
    sort($found); sort($expected);
    
    if ($found === $expected) {
        printStatus("Assertion PASSED: Fallback correctly found default extensions.", "OK_DETAIL");
        saveResult($summaryDesc, true);
    } else {
        printStatus("Assertion FAILED: Fallback extensions do not match.", "FAIL_DETAIL");
        printStatus("Expected: ".implode(', ',$expected), "FAIL_DETAIL"); 
        printStatus("Found:    ".implode(', ',$found), "FAIL_DETAIL");
        saveResult($summaryDesc, false);
    }
}

// --- Kernel Generic Implementation Discovery Tests ---

/**
 * Tests kernel's generic implementation discovery in a specified namespace.
 * @param array $envConfig Test environment configuration.
 * @param DiscoveryKernel $kernel Instantiated DiscoveryKernel.
 */
function testKernelGenericImpl_FindsInServicesNamespace(array $envConfig, DiscoveryKernel $kernel): void
{
    $interfaceFqcn = $envConfig['namespacePrefix'] . '\\Services\\GenericDiscoverableInterface';
    $targetNs = $envConfig['namespacePrefix'] . '\\Services';
    $summaryDesc = "Kernel Generic: Finds 'GenericDiscoverableInterface' in '{$targetNs}'";
    printStatus("Detail: Kernel generic scan in '{$targetNs}' for '{$interfaceFqcn}'", 'DEBUG');

    if (!method_exists($kernel, 'discoverImplementations')) {
        printStatus("Prerequisite FAILED: Method 'discoverImplementations' not on DiscoveryKernel.", "FAIL_DETAIL");
        saveResult($summaryDesc . " (Method Missing)", false);
        return;
    }
    $results = $kernel->discoverImplementations($interfaceFqcn, $targetNs);
    $found = []; foreach ($results as $o) {$found[] = $o->value();}
    $expected = [
        $envConfig['namespacePrefix'].'\\Services\\ServiceImpl1', 
        $envConfig['namespacePrefix'].'\\Services\\Sub\\ServiceImpl2'
    ];
    sort($found); sort($expected);

    if ($found === $expected) {
        printStatus("Assertion PASSED: Found expected generic implementations.", "OK_DETAIL");
        saveResult($summaryDesc, true);
    } else {
        printStatus("Assertion FAILED: Generic implementations do not match.", "FAIL_DETAIL");
        printStatus("Expected: ".implode(', ',$expected), "FAIL_DETAIL"); 
        printStatus("Found:    ".implode(', ',$found), "FAIL_DETAIL");
        saveResult($summaryDesc, false);
    }
}

/**
 * Tests kernel's generic implementation discovery in a targeted sub-namespace.
 * @param array $envConfig Test environment configuration.
 * @param DiscoveryKernel $kernel Instantiated DiscoveryKernel.
 */
function testKernelGenericImpl_FindsInServicesSubNamespace(array $envConfig, DiscoveryKernel $kernel): void
{
    $interfaceFqcn = $envConfig['namespacePrefix'] . '\\Services\\GenericDiscoverableInterface';
    $targetNs = $envConfig['namespacePrefix'] . '\\Services\\Sub';
    $summaryDesc = "Kernel Generic: Finds 'GenericDiscoverableInterface' in '{$targetNs}'";
    printStatus("Detail: Kernel generic scan in '{$targetNs}' for '{$interfaceFqcn}'", 'DEBUG');

    if (!method_exists($kernel, 'discoverImplementations')) {
        printStatus("Prerequisite FAILED: Method 'discoverImplementations' not on DiscoveryKernel.", "FAIL_DETAIL");
        saveResult($summaryDesc . " (Method Missing)", false);
        return;
    }
    $results = $kernel->discoverImplementations($interfaceFqcn, $targetNs);
    $found = []; foreach ($results as $o) {$found[] = $o->value();}
    $expected = [$envConfig['namespacePrefix'].'\\Services\\Sub\\ServiceImpl2'];
    sort($found); sort($expected);

    if ($found === $expected) {
        printStatus("Assertion PASSED: Found expected generic implementations in sub-namespace.", "OK_DETAIL");
        saveResult($summaryDesc, true);
    } else {
        printStatus("Assertion FAILED: Generic implementations in sub-namespace do not match.", "FAIL_DETAIL");
        printStatus("Expected: ".implode(', ',$expected), "FAIL_DETAIL"); 
        printStatus("Found:    ".implode(', ',$found), "FAIL_DETAIL");
        saveResult($summaryDesc, false);
    }
}

/**
 * Tests kernel's generic implementation discovery in a non-existent/empty namespace.
 * @param array $envConfig Test environment configuration.
 * @param DiscoveryKernel $kernel Instantiated DiscoveryKernel.
 */
function testKernelGenericImpl_NonExistentServicesNamespaceIsEmpty(array $envConfig, DiscoveryKernel $kernel): void
{
    $interfaceFqcn = $envConfig['namespacePrefix'] . '\\Services\\GenericDiscoverableInterface';
    $targetNs = $envConfig['namespacePrefix'] . '\\Services\\NonExistent';
    $summaryDesc = "Kernel Generic: Non-existent '{$targetNs}' yields empty";
    printStatus("Detail: Kernel generic scan in '{$targetNs}' for '{$interfaceFqcn}' (expect empty)", 'DEBUG');

    if (!method_exists($kernel, 'discoverImplementations')) {
        printStatus("Prerequisite FAILED: Method 'discoverImplementations' not on DiscoveryKernel.", "FAIL_DETAIL");
        saveResult($summaryDesc . " (Method Missing)", false);
        return;
    }
    $results = $kernel->discoverImplementations($interfaceFqcn, $targetNs);
    $isEmpty = method_exists($results, 'isEmpty') ? $results->isEmpty() : empty((array)$results);

    if ($isEmpty) {
        printStatus("Assertion PASSED: Non-existent generic namespace correctly yields empty set.", "OK_DETAIL");
        saveResult($summaryDesc, true);
    } else {
        printStatus("Assertion FAILED: Non-existent generic namespace was not empty.", "FAIL_DETAIL");
        saveResult($summaryDesc, false);
    }
}

// --- Main Test Execution Logic ---

printStatus("Starting Discovery Scanner tests.", 'INFO');

// Bootstrap
$summaryDescBootstrap = "Core Bootstrap: Initialization";
try {
    printStatus("Core bootstrap (test-bootstrap.php) assumed successful.", 'OK');
    saveResult($summaryDescBootstrap . " successful", true);
} catch (\Throwable $e) {
    saveResult($summaryDescBootstrap . " failed (Exception)", false);
    handleExceptionInTest($e, $summaryDescBootstrap);
    printStatus("Core bootstrap failed. Subsequent tests may be unreliable or skipped.", 'ERROR');
    $canProceed = false;
}

// Test Suite 1: Non-Kernel Generic Implementation Discovery
if ($canProceed) {
    printStatus("Executing Suite: Non-Kernel Generic Implementation Discovery", 'STEP', '1');
    $noKernelEnv = null;
    try {
        $noKernelEnv = createTestEnvironment('no_kernel_generic');
        printStatus("Non-Kernel Env - Temp Namespace: {$noKernelEnv['namespacePrefix']}", 'INFO');
        writeBasicTestFiles($noKernelEnv['psr4Dir'], $noKernelEnv['namespacePrefix']);
        registerAutoloader($noKernelEnv['namespacePrefix'], $noKernelEnv['psr4Dir']);
        printStatus("Non-Kernel Env - Setup complete.", "OK");

        runTest(
            "Non-Kernel: Finds 'DummyInterface' in '{$noKernelEnv['namespacePrefix']}\\Foo'",
            function() use ($noKernelEnv) { testNonKernel_FindsImplementationsInFoo($noKernelEnv); }
        );

    } catch (\Throwable $e) {
        // Catch errors during setup of this entire test suite
        $setupErrorDesc = "Non-Kernel Discovery: Suite Setup (Exception)";
        saveResult($setupErrorDesc, false);
        handleExceptionInTest($e, $setupErrorDesc);
        printStatus("Error during Non-Kernel Discovery Suite setup. Suite aborted.", 'ERROR');
    } finally {
        if ($noKernelEnv && !empty($noKernelEnv['baseDir'])) {
            cleanup($noKernelEnv['baseDir']);
        }
    }
} else {
    printStatus("Skipping Suite: Non-Kernel Generic Implementation Discovery (Bootstrap Failed)", "WARN");
    saveResult("Non-Kernel Discovery: Suite Setup (Skipped)", false);
    saveResult("Non-Kernel Discovery: Finds 'DummyInterface' (Skipped)", false);
}

// Test Suite 2: Kernel-based Discovery
if ($canProceed) {
    printStatus("Executing Suite: Kernel-based Discovery", 'STEP', '2');
    $kernelEnv = null;
    $kernel = null;
    try {
        $kernelEnv = createTestEnvironment('with_kernel_all');
        printStatus("Kernel Env - Temp Namespace: {$kernelEnv['namespacePrefix']}", 'INFO');
        writeKernelTestFiles($kernelEnv['psr4Dir'], $kernelEnv['namespacePrefix']);
        registerAutoloader($kernelEnv['namespacePrefix'], $kernelEnv['psr4Dir']);
        $kernel = new DiscoveryKernel($kernelEnv['namespacePrefix'], $kernelEnv['psr4Dir']); //
        printStatus("Kernel Env - DiscoveryKernel initialized for '{$kernelEnv['namespacePrefix']}'.", 'INFO');
        printStatus("Kernel Env - Setup complete (Extensions & Services).", "OK");
        saveResult("Kernel Discovery: Suite Setup (Ext & Services)", true);

        // Run Kernel Extension Discovery Tests
        printStatus("Kernel Env - Running Extension Discovery Tests", "INFO_SUB_HEADER");
        runTest("Kernel Ext: Default discovery finds all standard extensions", fn() => testKernelExtensions_DefaultDiscovery($kernelEnv, $kernel));
        runTest("Kernel Ext: Targeted discovery in '{$kernelEnv['namespacePrefix']}\\Extensions\\Domain'", fn() => testKernelExtensions_TargetedSubNamespace($kernelEnv, $kernel));
        runTest("Kernel Ext: Non-existent namespace '{$kernelEnv['namespacePrefix']}\\Extensions\\NonExistentExt' yields empty", fn() => testKernelExtensions_NonExistentNamespaceIsEmpty($kernelEnv, $kernel));
        runTest("Kernel Ext: Fallback from '{$kernelEnv['namespacePrefix']}\\Extensions\\NonExistentForFallback' finds default extensions", fn() => testKernelExtensions_FallbackDiscovery($kernelEnv, $kernel));
        
        // Run Kernel Generic Implementation Discovery Tests
        printStatus("Kernel Env - Running Generic Implementation Discovery Tests", "INFO_SUB_HEADER");
        if (method_exists($kernel, 'discoverImplementations')) {
            runTest("Kernel Generic: Finds 'GenericDiscoverableInterface' in '{$kernelEnv['namespacePrefix']}\\Services'", fn() => testKernelGenericImpl_FindsInServicesNamespace($kernelEnv, $kernel));
            runTest("Kernel Generic: Finds 'GenericDiscoverableInterface' in '{$kernelEnv['namespacePrefix']}\\Services\\Sub'", fn() => testKernelGenericImpl_FindsInServicesSubNamespace($kernelEnv, $kernel));
            runTest("Kernel Generic: Non-existent '{$kernelEnv['namespacePrefix']}\\Services\\NonExistent' yields empty", fn() => testKernelGenericImpl_NonExistentServicesNamespaceIsEmpty($kernelEnv, $kernel));
        } else {
            printStatus("Skipping Kernel Generic Implementation tests: 'discoverImplementations' method not found on Kernel.", "WARN");
            saveResult("Kernel Generic Impl: 'discoverImplementations' method missing (Skipped)", false);
        }

    } catch (\Throwable $e) {
        $setupErrorDesc = "Kernel Discovery: Suite Setup (Exception)";
        saveResult($setupErrorDesc, false);
        handleExceptionInTest($e, $setupErrorDesc);
        printStatus("Error during Kernel Discovery Suite setup. Suite aborted.", 'ERROR');
        // Mark all kernel sub-tests as skipped if main setup failed
        $skippedMsg = " (Skipped due to Suite Setup Fail)";
        saveResult("Kernel Extensions: Default discovery" . $skippedMsg, false);
        saveResult("Kernel Extensions: Targeted discovery" . $skippedMsg, false);
        // ... and so on for all planned kernel tests
    } finally {
        if ($kernelEnv && !empty($kernelEnv['baseDir'])) {
            cleanup($kernelEnv['baseDir']);
        }
    }
} else {
    printStatus("Skipping Suite: Kernel-based Discovery (Bootstrap Failed)", "WARN");
    // Mark all kernel tests as skipped
    $skippedMsg = " (Skipped - Bootstrap Fail)";
    saveResult("Kernel Discovery: Suite Setup" . $skippedMsg, false);
    // ...
}


// --- Final Summary ---
printStatus("All discovery tests completed.", 'INFO');
printAll($testResult);
echo "</pre>";