<?php

use App\Infrastructure\Session\Domain\ValueObjects\GuestSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\SessionContext;
use App\Kernel\Infrastructure\SessionKernel;
use App\Kernel\Adapters\EventListeningKernel;
use App\Adapters\EventListening\Infrastructure\Dispatcher\DefaultEventDispatcher;
use App\Kernel\Infrastructure\LoggingKernel;
use App\Kernel\Adapters\Providers\SessionEventBindingProvider;

function printStatus(string $message, string $status = 'INFO'): void
{
    echo sprintf("[%s] %s%s", strtoupper($status), $message, PHP_EOL);
}

// STEP 1: Bootstrap configuration
$configContainer = require_once __DIR__ . '/test-bootstrap.php';
printStatus("Bootstrap executed successfully. Configuration container loaded.", 'STEP');

// STEP 2: Initialize Logging
try {
    $loggingKernel = new LoggingKernel($configContainer);
    $logger = $loggingKernel->getLoggerAdapter('psr');
    printStatus("LoggingKernel initialized with PSR logger.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize LoggingKernel: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// STEP 3: Initialize EventListeningKernel + Dispatcher for Session
try {
    $eventListeningKernel = new EventListeningKernel([
        new SessionEventBindingProvider($logger),
    ]);

    $dispatcher = new DefaultEventDispatcher(
        $eventListeningKernel->resolver()
    );

    printStatus("Session event dispatcher initialized.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize session dispatcher: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// STEP 4: Initialize SessionKernel
try {
    $sessionResolver = $configContainer->getSessionHandlerResolver();
    $sessionKernel = new SessionKernel($sessionResolver, $dispatcher);
    printStatus("SessionKernel initialized.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize SessionKernel: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// STEP 5: Start Session
try {
    $sessionKernel->start();
    printStatus("Session started.", 'OK');
} catch (Throwable $e) {
    printStatus("Session start failed: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// STEP 6: Write new guest session data
try {
    $guest = new GuestSessionData(
        new SessionContext(locale: 'pt_BR', authenticated: false)
    );
    $sessionKernel->setData($guest);
    printStatus("Guest session data set.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to set session data: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// STEP 7: Retrieve and display session data
try {
    $data = $sessionKernel->getData();
    printStatus("Session data retrieved:", 'RESULT');
    echo '<pre>';
    print_r($data->toArray());
    echo '</pre>';
} catch (Throwable $e) {
    printStatus("Failed to retrieve session data: {$e->getMessage()}", 'FAIL');
    exit(1);
}

// STEP 8: Destroy the session
try {
    $sessionKernel->destroy();
    printStatus("Session destroyed.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to destroy session: {$e->getMessage()}", 'FAIL');
    exit(1);
}
