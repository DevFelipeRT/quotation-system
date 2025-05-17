<?php

use App\Infrastructure\Session\Domain\ValueObjects\GuestSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\AuthenticatedSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\SessionContext;
use App\Infrastructure\Session\Domain\ValueObjects\UserIdentity;
use App\Kernel\Infrastructure\SessionKernel;
use App\Kernel\Adapters\EventListeningKernel;
use App\Adapters\EventListening\Infrastructure\Dispatcher\DefaultEventDispatcher;
use App\Kernel\Infrastructure\LoggingKernel;
use App\Kernel\Adapters\Providers\SessionEventBindingProvider;
use Tests\Controllers\SessionTestController;

// === Utilitário de diagnóstico ===
function printStatus(string $message, string $status = 'INFO'): void
{
    echo sprintf("[%s] %s%s", strtoupper($status), $message, PHP_EOL);
}

function printSession(string $title): void
{
    echo "[SESSION] {$title}\n";
    echo '<pre>'; print_r($_SESSION); echo '</pre>';
}

// === Limpeza da sessão (essencial para evitar resíduos de execuções anteriores) ===
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}
$_SESSION = [];

// === STEP 1: Bootstrap configuration ===
$configContainer = require_once __DIR__ . '/test-bootstrap.php';
printStatus("Bootstrap executed successfully. Configuration container loaded.", 'STEP');

$sessionConfig = $configContainer->getSessionConfig();

// === STEP 2: Initialize Logging ===
try {
    $loggingKernel = new LoggingKernel($configContainer);
    $logger = $loggingKernel->getLoggerAdapter('psr');
    printStatus("LoggingKernel initialized with PSR logger.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize LoggingKernel: {$e->getMessage()}", 'FAIL');
    printSession("State after logging failure");
    exit(1);
}

// === STEP 3: Initialize EventListeningKernel + Dispatcher ===
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
    printSession("State after dispatcher failure");
    exit(1);
}

// === STEP 4: Initialize SessionKernel ===
try {
    $sessionKernel = new SessionKernel($sessionConfig, $dispatcher);
    printStatus("SessionKernel initialized.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to initialize SessionKernel: {$e->getMessage()}", 'FAIL');
    printSession("State after kernel failure");
    exit(1);
}

/**
 * ======== FACILITATED FLOW (using kernel helpers) ========
 */

// STEP 5a: Start guest session (helper)
try {
    $sessionKernel->startGuestSession();
    printStatus("Guest session started (helper method).", 'OK');
    $data = $sessionKernel->getData();
    printStatus("Session data (guest, facilitated):", 'RESULT');
    echo '<pre>'; print_r($data->toArray()); echo '</pre>';
} catch (Throwable $e) {
    printStatus("Failed to start guest session (facilitated): {$e->getMessage()}", 'FAIL');
    printSession("Session after failed guest session (facilitated)");
    exit(1);
}

// STEP 5b: Start authenticated session (helper)
try {
    $identity = new UserIdentity(1, 'Alice', 'admin');
    $sessionKernel->startAuthenticatedSession($identity);
    printStatus("Authenticated session started (helper method).", 'OK');
    $data = $sessionKernel->getData();
    printStatus("Session data (authenticated, facilitated):", 'RESULT');
    echo '<pre>'; print_r($data->toArray()); echo '</pre>';
} catch (Throwable $e) {
    printStatus("Failed to start authenticated session (facilitated): {$e->getMessage()}", 'FAIL');
    printSession("Session after failed authenticated session (facilitated)");
    exit(1);
}

/**
 * ======== ADVANCED FLOW (manual VO creation) ========
 */

// STEP 6a: Start session as guest with custom locale
try {
    $customLocale = 'en_US';
    $guest = new GuestSessionData(
        new SessionContext($customLocale, false)
    );
    $sessionKernel->setData($guest);
    printStatus("Guest session started (advanced/manual).", 'OK');
    $data = $sessionKernel->getData();
    printStatus("Session data (guest, advanced):", 'RESULT');
    echo '<pre>'; print_r($data->toArray()); echo '</pre>';
} catch (Throwable $e) {
    printStatus("Failed to set guest session (advanced): {$e->getMessage()}", 'FAIL');
    printSession("Session after failed guest session (advanced)");
    exit(1);
}

// STEP 6b: Start session as authenticated user with custom locale
try {
    $customLocale = 'es_ES';
    $identity = new UserIdentity(42, 'Bob', 'editor');
    $authenticated = new AuthenticatedSessionData(
        $identity,
        new SessionContext($customLocale, true)
    );
    $sessionKernel->setData($authenticated);
    printStatus("Authenticated session started (advanced/manual).", 'OK');
    $data = $sessionKernel->getData();
    printStatus("Session data (authenticated, advanced):", 'RESULT');
    echo '<pre>'; print_r($data->toArray()); echo '</pre>';
} catch (Throwable $e) {
    printStatus("Failed to set authenticated session (advanced): {$e->getMessage()}", 'FAIL');
    printSession("Session after failed authenticated session (advanced)");
    exit(1);
}

// ======== CONTROLLER-SPECIFIC DATA TEST (using SessionTestController) ========

// STEP 6c: Store and retrieve a custom value (favorite_color) using the controller
try {
    // Create controller with kernel dependency
    $controller = new SessionTestController($sessionKernel);

    // Store custom value in the session via the controller
    $controller->setFavoriteColor('blue');
    printStatus("Controller set favorite_color to 'blue'.", 'OK');
    printSession("Session after controller set favorite_color");

    // Retrieve custom value from the session via the controller
    $controller->getFavoriteColor();
    printStatus("Controller retrieved favorite_color from session.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to set/retrieve favorite_color using controller: {$e->getMessage()}", 'FAIL');
    printSession("Session after failed controller data operation");
    exit(1);
}


// STEP 7: Destroy the session
try {
    $sessionKernel->destroy();
    printStatus("Session destroyed.", 'OK');
} catch (Throwable $e) {
    printStatus("Failed to destroy session: {$e->getMessage()}", 'FAIL');
    printSession("Session after failed destroy");
    exit(1);
}
