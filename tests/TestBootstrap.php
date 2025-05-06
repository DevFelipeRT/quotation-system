<?php

declare(strict_types=1);

use Config\Container\ConfigContainer;

try {
    require_once __DIR__ . '/../bootstrap.php';

    /**
     * TestBootstrap
     *
     * Provides a shared instance of the application configuration container
     * for use in real integration tests of system modules.
     */
    $container = new ConfigContainer();

    echo "Bootstrap executed successfully.\n";
    return $container;
} catch (Throwable $e) {
    echo "An error occurred during bootstrap: " . $e->getMessage() . "\n";
    return null;
}
