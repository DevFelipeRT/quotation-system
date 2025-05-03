<?php

declare(strict_types=1);

/**
 * Front Controller
 *
 * Central entry point for all HTTP requests. Responsible for:
 * - Initializing infrastructure and application services
 * - Registering routes and use cases
 * - Normalizing and dispatching HTTP requests
 * - Delegating to appropriate controllers
 * - Handling uncaught exceptions gracefully
 */

use App\Application\Routing\RoutePath;
use App\Presentation\Http\Routing\HttpMethod;
use App\Presentation\Http\Routing\RouteRequest;
use App\Kernel\InfrastructureKernel;
use App\Kernel\DatabaseKernel;
use App\Kernel\UseCaseKernel;
use App\Kernel\ControllerKernel;
use App\Kernel\RouterKernel;
use Config\Container\ConfigContainer;

// ─────────────────────────────────────────────────────────────────────────────
// Bootstrap Configuration
// ─────────────────────────────────────────────────────────────────────────────

/** @var ConfigContainer $config */
$config = require_once __DIR__ . '/../bootstrap.php';

try {
    // ─────────────────────────────────────────────────────────────────────────
    // Kernel Initialization
    // ─────────────────────────────────────────────────────────────────────────

    $infra      = new InfrastructureKernel($config);
    $database   = new DatabaseKernel($config, $infra->logger());
    $useCases   = new UseCaseKernel($database->connection(), $infra->logger());
    $controllers = new ControllerKernel(
        $config,
        $infra->session(),
        $infra->viewRenderer(),
        $infra->urlResolver(),
        $infra->logger(),
        $infra->logAssembler(),
        $useCases->list(),
        $useCases->create(),
        $useCases->update(),
        $useCases->delete()
    );
    $router = new RouterKernel($controllers->map());

    // ─────────────────────────────────────────────────────────────────────────
    // HTTP Request Normalization
    // ─────────────────────────────────────────────────────────────────────────

    $scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $uri        = $_SERVER['REQUEST_URI'] ?? '/';
    $script     = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath   = str_replace('\\', '/', dirname($script));
    $path       = parse_url(preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri), PHP_URL_PATH) ?: '/';

    $request = new RouteRequest(
        new HttpMethod($_SERVER['REQUEST_METHOD'] ?? 'GET'),
        new RoutePath($path),
        $_SERVER['HTTP_HOST'] ?? 'localhost',
        $scheme
    );

    // ─────────────────────────────────────────────────────────────────────────
    // Request Dispatch
    // ─────────────────────────────────────────────────────────────────────────

    echo $router->engine()->handle($request);

} catch (Throwable $exception) {
    handleGlobalException($exception, $config);
}

// ─────────────────────────────────────────────────────────────────────────────
// Global Exception Handler
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Handles all uncaught exceptions globally and reports them to logs.
 *
 * @param Throwable        $exception
 * @param ConfigContainer  $config
 */
function handleGlobalException(Throwable $exception, ConfigContainer $config): void
{
    http_response_code(500);

    $logDir  = $config->paths()->logsDir();
    $logFile = $logDir . '/fatal.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    file_put_contents($logFile, (string) $exception . PHP_EOL, FILE_APPEND);

    if ($config->app()->isDevelopment()) {
        echo '<h1>Internal Server Error</h1>';
        echo '<pre>' . htmlspecialchars((string) $exception, ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        echo 'An internal error occurred. Please try again later.';
    }
}
