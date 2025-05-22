<?php

declare(strict_types=1);

use Config\ConfigProvider;
use Config\Env\EnvLoader;
use Config\Env\EnvValidator;
use Config\Paths\PathsConfig;
use Config\SystemConstants;

/**
 * Bootstrap File
 *
 * Initializes autoloading, core configuration, and environment checks
 * for the application runtime. This script is the first to execute and
 * should terminate immediately on misconfiguration or failure.
 */

// ─────────────────────────────────────────────
// Define application root path
// ─────────────────────────────────────────────

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

// ─────────────────────────────────────────────
// Register PSR-4 autoloader
// ─────────────────────────────────────────────

require_once BASE_PATH . '/autoload.php';

// ─────────────────────────────────────────────
// Initialize default runtime environment
// ─────────────────────────────────────────────

date_default_timezone_set('UTC');

/**
 * Convert PHP runtime errors into exceptions.
 * Enforces fail-fast behavior across the stack.
 */
set_error_handler(static function (
    int $errno,
    string $errstr,
    string $errfile,
    int $errline
): bool {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

/**
 * Fallback handler for uncaught exceptions.
 * Avoids stack trace disclosure in production.
 */
set_exception_handler(static function (Throwable $e): void {
    http_response_code(500);
    echo 'Fatal application error. Execution halted.';
    file_put_contents(
        BASE_PATH . '/storage/logs/bootstrap.log',
        (string) $e . PHP_EOL .
        'Error: '    . $e->getMessage() . PHP_EOL .
        'Code: '     . $e->getCode() . PHP_EOL .
        'In line: '  . $e->getLine() . PHP_EOL .
        'In trace: ' . $e->getTraceAsString() . PHP_EOL .
        'In file: '  . $e->getFile() . PHP_EOL .
        FILE_APPEND
    );
});

// ─────────────────────────────────────────────
// Load configuration and environment
// ─────────────────────────────────────────────

try {
    // Step 1: Resolve paths
    $paths = new PathsConfig(BASE_PATH);

    // Step 2: Load and validate environment variables
    $env = new EnvLoader($paths->getEnvFilePath());
    EnvValidator::validate($env);

    // Step 3: Bootstrap full application config provider
    $configProvider = new ConfigProvider();

    // Step 4: Load system constants
    SystemConstants::initialize($configProvider);

    return $configProvider;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Failed to initialize application configuration.';

    file_put_contents(
        BASE_PATH . '/storage/logs/bootstrap.log',
        (string) $e . PHP_EOL,
        FILE_APPEND
    );

    exit(1);
}
