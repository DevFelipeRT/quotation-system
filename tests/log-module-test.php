<?php

use App\Kernel\Infrastructure\LoggingKernel;
use App\Application\Messaging\Application\Types\LoggableMessage;
use App\Infrastructure\Logging\Domain\LogEntry;
use App\Infrastructure\Logging\Domain\LogLevelEnum;

echo "<pre>";

function printStep(string $message): void
{
    echo "[STEP] $message" . PHP_EOL;
}

function printSuccess(string $message): void
{
    echo "[OK] $message" . PHP_EOL;
}

function printFailure(string $message): void
{
    echo "[FAIL] $message" . PHP_EOL;
    exit(1);
}

printStep("Loading configuration...");
$configContainer = require __DIR__ . '/test-bootstrap.php';

if (!$configContainer) {
    printFailure("Failed to load configuration container.");
}
printSuccess("Configuration loaded.");

// Initialize Logging Kernel
printStep("Initializing LoggingKernel...");
try {
    $loggingKernel = new LoggingKernel($configContainer);
    printSuccess("LoggingKernel initialized.");
} catch (Throwable $e) {
    printFailure("Initialization failed: {$e->getMessage()}");
}

// Retrieve Logger
printStep("Retrieving Logger...");
try {
    $logger = $loggingKernel->getLogger();
    printSuccess("Logger retrieved.");
} catch (Throwable $e) {
    printFailure("Logger retrieval failed: {$e->getMessage()}");
}

// Retrieve LogEntryAssembler
printStep("Retrieving LogEntryAssembler...");
try {
    $assembler = $loggingKernel->getLogEntryAssembler();
    printSuccess("LogEntryAssembler retrieved.");
} catch (Throwable $e) {
    printFailure("LogEntryAssembler retrieval failed: {$e->getMessage()}");
}

// Create LogEntry from LoggableMessage (via assembler)
printStep("Creating LogEntry from message...");
try {
    $message = LoggableMessage::warning('Test log message (via assembler)', ['test' => true]);
    $entryFromAssembler = $assembler->assembleFromMessage($message);
    printSuccess("LogEntry (from assembler) created.");
} catch (Throwable $e) {
    printFailure("Failed to assemble LogEntry: {$e->getMessage()}");
}

// Create LogEntry manually (direct instantiation)
printStep("Creating LogEntry manually...");
try {
    $entryManual = new LogEntry(
        level: LogLevelEnum::INFO,
        message: 'Manual log entry creation (no assembler)',
        context: ['source' => 'manual'],
        channel: 'test',
        timestamp: new DateTimeImmutable()
    );
    printSuccess("LogEntry (manual) created.");
} catch (Throwable $e) {
    printFailure("Failed to create LogEntry manually: {$e->getMessage()}");
}

// Persist both entries
printStep("Persisting both LogEntries...");
try {
    $logger->log($entryFromAssembler);
    $logger->log($entryManual);
    printSuccess("Both LogEntries persisted successfully.");
} catch (Throwable $e) {
    printFailure("Failed to persist LogEntries: {$e->getMessage()}");
}
