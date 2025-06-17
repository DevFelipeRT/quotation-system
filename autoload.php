<?php

/**
 * PSR-4 Compliant Native Autoloader with Dynamic Module Discovery
 *
 * This autoloader supports both:
 *   - Static namespace mappings for conventional application layers (e.g. App\, Config\, Tests\)
 *   - Dynamic namespace resolution for modular components organized by architectural layer
 *
 * Modules are expected to be located in:
 *   /modules/{Layer}/{Module}/
 * And are loaded using the module name as the namespace prefix.
 *
 * Example:
 *   - Class: Routing\Domain\Events\RouteMatchedEvent
 *   - Path:  /modules/Infrastructure/Routing/Domain/Events/RouteMatchedEvent.php
 */

spl_autoload_register(function (string $class): void {
    static $prefixes = null;

    if ($prefixes === null) {
        $prefixes = [];

        // 1. Static namespace mappings (PSR-4 style)
        $prefixes['App\\']    = __DIR__ . '/src/';
        $prefixes['Config\\'] = __DIR__ . '/config/';
        $prefixes['Tests\\']  = __DIR__ . '/tests/';
        $prefixes['PublicContracts\\'] = __DIR__ . '/modules/Shared/PublicContracts/';

        // 2. Dynamic namespace mappings for modules
        $modulesBasePath = __DIR__ . '/modules/';

        foreach (scandir($modulesBasePath) as $layer) {
            if ($layer === '.' || $layer === '..') {
                continue;
            }

            $layerPath = $modulesBasePath . $layer;
            if (!is_dir($layerPath)) {
                continue;
            }

            foreach (scandir($layerPath) as $module) {
                if ($module === '.' || $module === '..') {
                    continue;
                }

                $modulePath = $layerPath . DIRECTORY_SEPARATOR . $module;
                if (!is_dir($modulePath)) {
                    continue;
                }

                // Register namespace prefix based on module name
                // e.g., "Routing\" => "/modules/Infrastructure/Routing/"
                $namespacePrefix = $module . '\\';

                // Avoid duplicate namespace entries (in case of name collision across layers)
                if (!isset($prefixes[$namespacePrefix])) {
                    $prefixes[$namespacePrefix] = $modulePath;
                }
            }
        }
    }

    // Attempt to resolve the class using the namespace prefixes
    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relativeClass = substr($class, strlen($prefix));
            $relativePath  = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);
            $file          = $baseDir . DIRECTORY_SEPARATOR . $relativePath . '.php';

            if (file_exists($file)) {
                require_once $file;
            }

            return;
        }
    }
});
