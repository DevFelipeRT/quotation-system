<?php

use App\Kernel\DatabaseKernel;
use App\Kernel\LoggingKernel;

// Initialize the application configuration container
$configContainer = require_once __DIR__ . '/TestBootstrap.php';
$databaseConfig = $configContainer->getDatabaseConfig();

// Instantiates the Logging module
try {
    $loggingKernel = new LoggingKernel($configContainer);
    echo "LoggingKernel initialized successfully." . PHP_EOL;
} catch (Throwable $e) {
    echo "Error initializing LoggingKernel: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

$logger = $loggingKernel->getLogger();

// Instantiate the Database module
try {
    $databaseKernel = new DatabaseKernel($databaseConfig, $logger, true);
    echo "DatabaseKernel initialized successfully." . PHP_EOL;
} catch (\Throwable $e) {
    echo "Error initializing DatabaseKernel: " . $e->getMessage() . PHP_EOL;
    exit(1);
}