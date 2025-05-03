<?php

declare(strict_types=1);

use Config\Container\ConfigContainer;
use Config\Env\EnvLoader;
use Config\Env\EnvValidator;
use Config\Paths\PathsConfig;

/**
 * Application Bootstrap File
 *
 * Responsible for initializing runtime configuration, environment validation,
 * and autoload registration. This script is the first point of execution
 * for all HTTP and CLI interfaces and should fail fast if misconfigured.
 */

// ─────────────────────────────────────────────────────────────────────────────
// Define the application root path (BASE_PATH)
// ─────────────────────────────────────────────────────────────────────────────

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

// ─────────────────────────────────────────────────────────────────────────────
// Autoload PSR-4 classes
// ─────────────────────────────────────────────────────────────────────────────

$autoload = BASE_PATH . '/autoload.php';
require_once BASE_PATH . '/autoload.php';

// ─────────────────────────────────────────────────────────────────────────────
// Runtime environment defaults
// ─────────────────────────────────────────────────────────────────────────────

date_default_timezone_set('UTC');

// Convert PHP errors into exceptions (fail-fast principle)
set_error_handler(static function (
    int $errno,
    string $errstr,
    string $errfile,
    int $errline
): bool {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Handle uncaught exceptions gracefully
set_exception_handler(static function (Throwable $e): void {
    http_response_code(500);
    echo 'Fatal application error. Execution halted.';
});

// ─────────────────────────────────────────────────────────────────────────────
// Configuration loading and environment validation
// ─────────────────────────────────────────────────────────────────────────────

try {
    // Step 1: Resolve base paths
    $paths = new PathsConfig();

    // Step 2: Load and validate environment variables
    $env = new EnvLoader($paths->envFile());
    EnvValidator::validate($env);

    // Step 3: Create and return the full configuration container
    return new ConfigContainer();

} catch (Throwable $e) {
    http_response_code(500);
    echo 'Failed to initialize application configuration.';

    // Attempt to log the failure in a fallback location
    $fallbackLog = BASE_PATH . '/storage/logs/bootstrap.log';
    file_put_contents($fallbackLog, (string) $e . PHP_EOL, FILE_APPEND);

    exit(1);
}
