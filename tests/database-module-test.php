<?php

use App\Kernel\Infrastructure\Database\DatabaseConnectionKernel;
use App\Kernel\Infrastructure\Database\DatabaseExecutionKernel;
use App\Kernel\Infrastructure\LoggingKernel;

function printStatus(string $message, string $status = 'INFO'): void
{
    echo sprintf("[%s] %s%s", strtoupper($status), $message, PHP_EOL);
}

// Step 1: Load configuration
$configContainer = require_once __DIR__ . '/test-bootstrap.php';
printStatus("Bootstrap executed successfully. Configuration container loaded.", 'STEP');

$databaseConfig = $configContainer->getDatabaseConfig();

// Step 2: Initialize logging
try {
    $loggingKernel = new LoggingKernel($configContainer);
    printStatus("LoggingKernel initialized successfully.", 'OK');
} catch (Throwable $e) {
    printStatus("Error initializing LoggingKernel: {$e->getMessage()}", 'FAIL');
    exit(1);
}

$logger = $loggingKernel->getLogger();

// Step 3: Initialize connection kernel
try {
    $databaseConnectionKernel = new DatabaseConnectionKernel($databaseConfig, $logger, true);
    printStatus("DatabaseConnectionKernel initialized successfully.", 'OK');
} catch (Throwable $e) {
    printStatus("Error initializing DatabaseConnectionKernel: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// Step 4: Retrieve connection instance
try {
    $connection = $databaseConnectionKernel->getConnection();
    printStatus("Database connection initialized successfully.", 'OK');
} catch (Throwable $e) {
    printStatus("Error initializing Database connection: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// Step 5: Initialize execution kernel
try {
    $databaseExecutionKernel = new DatabaseExecutionKernel($connection, $logger);
    printStatus("DatabaseExecutionKernel initialized successfully.", 'OK');
} catch (Throwable $e) {
    printStatus("Error initializing DatabaseExecutionKernel: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// Step 6: Retrieve request interface
try {
    $request = $databaseExecutionKernel->request();
    printStatus("Database request initialized successfully.", 'OK');
} catch (Throwable $e) {
    printStatus("Error initializing Database request: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// Step 7: Execute SELECT query
try {
    $sql = "SELECT 1";
    $result = $request->select($sql);
    printStatus("Select query executed successfully.", 'OK');
} catch (Throwable $e) {
    printStatus("Error executing select query: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// Step 8: Print results
if (!empty($result)) {
    printStatus("Query returned results:", 'RESULT');
    echo '<pre>';
    print_r($result);
    echo '</pre>';
} else {
    printStatus("No results found.", 'INFO');
}