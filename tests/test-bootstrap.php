<?php

declare(strict_types=1);

namespace App\Tests;

use Config\ConfigProvider;
use Throwable;

try {
    require_once __DIR__ . '/../bootstrap.php';

    /**
     * TestBootstrap
     *
     * Provides a shared instance of the application configuration provider
     * for use in real integration tests of system modules.
     */
    $provider = new ConfigProvider();
    return $provider;
} catch (Throwable $e) {
    echo "An error occurred during bootstrap: " . $e->getMessage() . "\n";
    return null;
}
