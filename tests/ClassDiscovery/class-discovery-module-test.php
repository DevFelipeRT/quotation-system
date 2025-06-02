<?php

declare(strict_types=1);

/**
 * Integration Test — ClassDiscoveryFacade
 *
 * This script performs a runtime verification of the ClassDiscovery module,
 * ensuring the facade correctly discovers PHP classes that either implement a given interface
 * or extend a base class within a specified namespace. This test bypasses the container
 * and manually instantiates components as per kernel logic.
 *
 * Output formatting follows the project’s test reporting standard with [STEP], [OK], and [RESULT] tags.
 *
 * Target contract: PublicContracts\ClassDiscovery\ClassDiscoveryFacadeInterface
 * Test domain:     Tests\ClassDiscovery\
 * Base directory:  /tests/ClassDiscovery
 *
 * Test coverage:
 * - Manual instantiation of facade (kernel-style)
 * - Discovery by interface implementation
 * - Discovery by inheritance (subclass resolution)
 */

use PublicContracts\ClassDiscovery\ClassDiscoveryFacadeInterface;
use ClassDiscovery\Application\Service\ClassDiscoveryFacade;
use ClassDiscovery\Application\Service\ClassDiscoveryService;
use ClassDiscovery\Infrastructure\FileToFqcnResolverPsr4;
use ClassDiscovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use ClassDiscovery\Infrastructure\PhpFileFinderRecursive;
use Tests\ClassDiscovery\Interfaces\TestInterface;
use Tests\ClassDiscovery\Classes\AbstractTestClass;

echo "<pre>";

$results = [];

/**
 * Outputs a formatted log line for test status.
 *
 * @param string $message
 * @param string $tag One of: INFO, STEP, OK, RESULT, ERROR
 */
function printStatus(string $message, string $tag = 'INFO'): void
{
    echo sprintf("[%s] %s\n", strtoupper($tag), $message);
}

/**
 * Prints a summary line for a test result.
 *
 * @param string $description
 * @param bool $result
 */
function printResult(string $description, bool $result): void
{
    echo str_pad($description, 80, '.') . ($result ? "OK\n" : "FAIL\n");
}

/**
 * Stores a test result in the global test result array.
 *
 * @param string $description
 * @param bool $result
 */
function saveResult(string $description, bool $result): void
{
    global $results;
    $results[] = ['description' => $description, 'result' => $result];
}

/**
 * Displays exception details with consistent formatting.
 *
 * @param \Throwable $e
 */
function handleException(\Throwable $e): void
{
    printStatus("Exception: " . get_class($e) . " - " . $e->getMessage(), 'ERROR');
    echo "    in {$e->getFile()}:{$e->getLine()}\n";
}

/**
 * Validates whether an array contains only class/interface names (non-empty strings).
 *
 * @param array $arr
 * @return bool
 */
function allStrings(array $arr): bool
{
    foreach ($arr as $value) {
        if (!is_string($value) || $value === '' || (!class_exists($value) && !interface_exists($value))) {
            return false;
        }
    }
    return true;
}

// === BOOTSTRAP ===
printStatus("Starting ClassDiscovery test.", 'INFO');
printStatus("Loading test environment...", 'STEP');

try {
    require __DIR__ . '/../test-bootstrap.php';
    printStatus("Bootstrap completed successfully.", 'OK');
    saveResult("Bootstrap execution", true);
} catch (\Throwable $e) {
    handleException($e);
    saveResult("Bootstrap execution", false);
    exit;
}

// === STEP 1: Manual facade instantiation ===
printStatus("Creating discovery facade manually (as per kernel logic)...", 'STEP');

try {
    $psr4Prefix = 'Tests\\ClassDiscovery\\';
    $baseSourceDir = __DIR__;

    $resolver     = new NamespaceToDirectoryResolverPsr4($psr4Prefix, $baseSourceDir);
    $fileFinder   = new PhpFileFinderRecursive();
    $fqcnResolver = new FileToFqcnResolverPsr4();

    $scanner = new ClassDiscoveryService($resolver, $fqcnResolver, $fileFinder);
    $facade  = new ClassDiscoveryFacade($scanner);

    printStatus("Facade instantiated.", 'OK');
    $ok = $facade instanceof ClassDiscoveryFacadeInterface;
    saveResult("Facade created manually following kernel structure", $ok);
} catch (\Throwable $e) {
    handleException($e);
    saveResult("Facade created manually following kernel structure", false);
    exit;
}

// === STEP 2: Interface implementation discovery ===
printStatus("Testing interface-based discovery...", 'STEP');

try {
    $resultsArray = $facade->implementing(TestInterface::class, 'Tests\\ClassDiscovery\\Classes');

    printStatus("implementing() returned " . count($resultsArray) . " result(s).", 'OK');
    $ok = is_array($resultsArray) && !empty($resultsArray) && allStrings($resultsArray);
    saveResult("Discovering implementations returns valid string[]", $ok);

    printStatus("Discovered FQCNs: " . implode(', ', $resultsArray), 'RESULT');
} catch (\Throwable $e) {
    handleException($e);
    saveResult("Discovering implementations returns valid string[]", false);
}

// === STEP 3: Subclass discovery ===
printStatus("Testing subclass-based discovery...", 'STEP');

try {
    $resultsArray = $facade->extending(AbstractTestClass::class, 'Tests\\ClassDiscovery\\Classes');

    printStatus("extending() returned " . count($resultsArray) . " result(s).", 'OK');
    $ok = is_array($resultsArray) && !empty($resultsArray) && allStrings($resultsArray);
    saveResult("Discovering subclasses returns valid string[]", $ok);

    printStatus("Discovered FQCNs: " . implode(', ', $resultsArray), 'RESULT');
} catch (\Throwable $e) {
    handleException($e);
    saveResult("Discovering subclasses returns valid string[]", false);
}

// === STEP 4: Interface implementation discovery without namespace ===
printStatus("Testing interface-based discovery without namespace...", 'STEP');

try {
    $resultsArray = $facade->implementing(TestInterface::class);

    printStatus("implementing() returned " . count($resultsArray) . " result(s).", 'OK');
    $ok = is_array($resultsArray) && !empty($resultsArray) && allStrings($resultsArray);
    saveResult("Discovering implementations without namespace returns valid string[]", $ok);

    printStatus("Discovered FQCNs: " . implode(', ', $resultsArray), 'RESULT');
} catch (\Throwable $e) {
    handleException($e);
    saveResult("Discovering implementations without namespace returns valid string[]", false);
}

// === STEP 5: Subclass discovery without namespace ===
printStatus("Testing subclass-based discovery without namespace...", 'STEP');

try {
    $resultsArray = $facade->extending(AbstractTestClass::class);

    printStatus("extending() returned " . count($resultsArray) . " result(s).", 'OK');
    $ok = is_array($resultsArray) && !empty($resultsArray) && allStrings($resultsArray);
    saveResult("Discovering subclasses without namespace returns valid string[]", $ok);

    printStatus("Discovered FQCNs: " . implode(', ', $resultsArray), 'RESULT');
} catch (\Throwable $e) {
    handleException($e);
    saveResult("Discovering subclasses without namespace returns valid string[]", false);
}

// === SUMMARY ===
printStatus("All tests completed.", 'INFO');
echo PHP_EOL;
echo "--- CLASS DISCOVERY TEST SUMMARY ---" , PHP_EOL;

foreach ($results as $r) {
    printResult($r['description'], $r['result']);
}

echo "</pre>";
