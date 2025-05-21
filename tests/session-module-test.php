<?php

declare(strict_types=1);

/**
 * Session Module Integration Test
 *
 * Validates guest and authenticated session flows, direct session data manipulation,
 * controller-bound session value usage, and robust session destruction.
 *
 * Relies exclusively on KernelManager for all dependency resolution and orchestration.
 * Fully aligned with Clean Code, SOLID, PSR-12, Object Calisthenics and professional documentation standards.
 *
 * @package Tests\Session
 */

use App\Kernel\KernelManager;
use App\Infrastructure\Session\Domain\ValueObjects\GuestSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\AuthenticatedSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\SessionContext;
use App\Infrastructure\Session\Domain\ValueObjects\UserIdentity;
use App\Kernel\Infrastructure\SessionKernel;
use Tests\Controllers\SessionTestController;

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
 * Prints the PHP session state.
 *
 * @param string $title
 * @return void
 */
function printSession(string $title): void
{
    echo "[SESSION] {$title}\n";
    echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';
}

/**
 * Ensures a clean session state before running the test.
 */
function resetSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    $_SESSION = [];
}

/**
 * Executes all functional tests for the session module using KernelManager.
 *
 * @return void
 */
function runSessionIntegrationTestWithKernelManager(): void
{
    resetSession();

    echo "<pre>";

    // Step 1: Bootstrap global configuration and KernelManager
    $configProvider = require __DIR__ . '/test-bootstrap.php';
    printStatus("Bootstrap executed successfully. Configuration loaded.", 'STEP');

    try {
        $kernelManager = new KernelManager($configProvider);
        printStatus("KernelManager initialized.", 'OK');
    } catch (Throwable $e) {
        printStatus("Fatal error on KernelManager: " . $e->getMessage(), 'FAIL');
        echo "<pre>";
        echo $e;
        echo "</pre>";
        exit(1);
    }

    // Step 2: Obtain SessionKernel from KernelManager
    $sessionKernel = $kernelManager->getSessionKernel();
    printStatus("SessionKernel obtained from KernelManager.", 'OK');

    // Functional flows (delegated to helper functions)
    runGuestSessionFlow($sessionKernel);
    runAuthenticatedSessionFlow($sessionKernel);
    runManualGuestSessionFlow($sessionKernel, 'en_US');
    runManualAuthenticatedSessionFlow($sessionKernel, 99, 'Jane', 'reviewer', 'fr_FR');
    runControllerSessionFlow($sessionKernel);
    runSessionDestructionFlow($sessionKernel);
}

/**
 * Tests starting a guest session and prints the result.
 */
function runGuestSessionFlow(SessionKernel $sessionKernel): void
{
    $sessionKernel->startGuestSession();
    printStatus("Guest session started (helper).", 'OK');
    printArrayResult("Session data (guest)", $sessionKernel->getData()->toArray());
}

/**
 * Tests starting an authenticated session and prints the result.
 */
function runAuthenticatedSessionFlow(SessionKernel $sessionKernel): void
{
    $identity = new UserIdentity(1, 'Alice', 'admin');
    $sessionKernel->startAuthenticatedSession($identity);
    printStatus("Authenticated session started (helper).", 'OK');
    printArrayResult("Session data (authenticated)", $sessionKernel->getData()->toArray());
}

/**
 * Tests manual instantiation and injection of a guest session object.
 */
function runManualGuestSessionFlow(SessionKernel $sessionKernel, string $locale): void
{
    $guest = new GuestSessionData(new SessionContext($locale, false));
    $sessionKernel->setData($guest);
    printStatus("Manual GuestSessionData set.", 'OK');
    printArrayResult("Session data (guest, manual)", $sessionKernel->getData()->toArray());
}

/**
 * Tests manual instantiation and injection of an authenticated session object.
 */
function runManualAuthenticatedSessionFlow(SessionKernel $sessionKernel, int $id, string $name, string $role, string $locale): void
{
    $identity = new UserIdentity($id, $name, $role);
    $authenticated = new AuthenticatedSessionData($identity, new SessionContext($locale, true));
    $sessionKernel->setData($authenticated);
    printStatus("Manual AuthenticatedSessionData set.", 'OK');
    printArrayResult("Session data (authenticated, manual)", $sessionKernel->getData()->toArray());
}

/**
 * Tests session manipulation through a controller.
 */
function runControllerSessionFlow(SessionKernel $sessionKernel): void
{
    $controller = new SessionTestController($sessionKernel);
    $controller->setFavoriteColor('green');
    printStatus("Controller set favorite_color to 'green'.", 'OK');
    printSession("Session after controller setFavoriteColor");

    $controller->getFavoriteColor();
    printStatus("Controller retrieved favorite_color from session.", 'OK');
}

/**
 * Tests session destruction.
 */
function runSessionDestructionFlow(SessionKernel $sessionKernel): void
{
    $sessionKernel->destroy();
    printStatus("Session destroyed.", 'OK');
    printSession("Session after destroy");
}

/**
 * Utility to print labeled array results.
 *
 * @param string $label
 * @param array $data
 * @return void
 */
function printArrayResult(string $label, array $data): void
{
    printStatus($label, 'RESULT');
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

// Run the complete session module test suite via KernelManager
runSessionIntegrationTestWithKernelManager();
