<?php

declare(strict_types=1);

/**
 * Database Module Integration Test
 *
 * Validates database connection and execution using KernelManager.
 *
 * Relies exclusively on KernelManager for all dependency resolution and orchestration.
 * Fully aligned with Clean Code, SOLID, PSR-12, Object Calisthenics and professional documentation standards.
 *
 * @package Tests
 */

use App\Kernel\KernelManager;

/**
 * Prints a test status message.
 *
 * @param string $message
 * @param string $status
 * @return void
 */
function printStatus(string $message, string $status = 'INFO'): void
{
    echo sprintf("[%s] %s%s", strtoupper($status), $message, PHP_EOL);
}

/**
 * Executes all functional tests for the module using KernelManager.
 *
 * @return void
 */
function runIntegrationTestWithKernelManager(): void
{
    echo "<pre>";

    // STEP 1: Bootstrap configuration
    $configContainer = require_once __DIR__ . '/test-bootstrap.php';
    printStatus("Bootstrap executed successfully. Configuration provider loaded.", 'STEP');

    // STEP 2: Initialize KernelManager
    try {
        $kernelManager = new KernelManager($configContainer);
        printStatus("KernelManager initialized.", 'OK');
    } catch (Throwable $e) {
        printStatus("Fatal error on KernelManager: " . $e->getMessage(), 'FAIL');
        echo "<pre>";
        echo $e;
        echo "</pre>";
        exit(1);
    }

    // STEP 2: Retrieve DatabaseConnectionKernel
    try {
        $databaseConnectionKernel = $kernelManager->getDatabaseConnectionKernel();
        printStatus("DatabaseConnectionKernel initialized successfully.", 'OK');
    } catch (Throwable $e) {
        printStatus("Failed to initialize DatabaseConnectionKernel: {$e->getMessage()}", 'FAIL');
        exit(1);
    }

    // STEP 5: Test Connection
    try {
        $connection = $databaseConnectionKernel->getConnection();
        if ($connection !== null) {
            printStatus("Database connection is active.", 'OK');
        } else {
            printStatus("Database connection is not active.", 'FAIL');
            exit(1);
        }
        printStatus("Database connection established.", 'OK');
    } catch (Throwable $e) {
        printStatus("Connection failed: {$e->getMessage()}", 'FAIL');
        exit(1);
    }

    // STEP 6: Retrieve Execution Kernel
    try {
        $executionKernel = $kernelManager->getDatabaseExecutionKernel();
        printStatus("DatabaseExecutionKernel initialized.", 'OK');
    } catch (Throwable $e) {
        printStatus("Failed to initialize execution kernel: {$e->getMessage()}", 'FAIL');
    }

    // STEP 7: Execute a test query
    try {
        $request = $executionKernel->request();
        $result = $request->select("SELECT 1");
        printStatus("Query executed successfully.", 'OK');
    } catch (Throwable $e) {
        printStatus("Query execution failed: {$e->getMessage()}", 'FAIL');
        exit(1);
    }

    // STEP 7: Execute a test query
    try {
        $request = $executionKernel->request();
        $result = $request->select("SELECT 1");
        printStatus("Query executed successfully.", 'OK');
    } catch (Throwable $e) {
        printStatus("Query execution failed: {$e->getMessage()}", 'FAIL');
        exit(1);
    }

    // STEP 8: Print query result
    if (!empty($result)) {
        printStatus("Query returned results:", 'RESULT');
        echo '<pre>';
        print_r($result);
        echo '</pre>';
    } else {
        printStatus("No results found.", 'INFO');
    }
}

runIntegrationTestWithKernelManager();