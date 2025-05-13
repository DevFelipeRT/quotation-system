<?php

use App\Adapters\EventListening\Infrastructure\Dispatcher\DefaultEventDispatcher;
use App\Kernel\Adapters\EventListeningKernel;
use App\Kernel\Adapters\Providers\DatabaseEventBindingProvider;
use App\Kernel\Infrastructure\Database\DatabaseConnectionKernel;
use App\Kernel\Infrastructure\Database\DatabaseExecutionKernel;
use App\Kernel\Infrastructure\LoggingKernel;

function printStatus(string $message, string $status = 'INFO'): void
{
    echo sprintf("[%s] %s%s", strtoupper($status), $message, PHP_EOL);
}

// STEP 1: Bootstrap configuration
$configContainer = require_once __DIR__ . '/test-bootstrap.php';
printStatus("Bootstrap executed successfully. Configuration container loaded.", 'STEP');

$databaseConfig = $configContainer->getDatabaseConfig();

// STEP 2: Initialize Logging
try {
    $loggingKernel = new LoggingKernel($configContainer);
    $logger = $loggingKernel->getLoggerAdapter('psr'); // Adaptador PSR-compatível
    printStatus("LoggingKernel initialized with PSR logger.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize LoggingKernel: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// STEP 3: Initialize EventListeningKernel + Dispatcher
try {
    $eventListeningKernel = new EventListeningKernel([
        new DatabaseEventBindingProvider($logger),
    ]);

    $dispatcher = new DefaultEventDispatcher($eventListeningKernel->resolver());
    printStatus("Event dispatcher initialized via EventListeningKernel.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize event dispatching: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// STEP 4: Initialize DatabaseConnectionKernel
try {
    $databaseConnectionKernel = new DatabaseConnectionKernel(
        config: $databaseConfig,
        dispatcher: $dispatcher,
        debugMode: true
    );
    printStatus("DatabaseConnectionKernel initialized successfully.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize DatabaseConnectionKernel: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// STEP 5: Get Connection
try {
    $connection = $databaseConnectionKernel->getConnection();
    printStatus("Database connection established.", 'OK');
} catch (Throwable $e) {
    printStatus("Connection failed: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// STEP 6: Initialize Execution Kernel
try {
    $executionKernel = new DatabaseExecutionKernel($connection, $dispatcher);
    printStatus("DatabaseExecutionKernel initialized.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize execution kernel: {$e->getMessage()}", 'FAIL');
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
