<?php

/**
 * Custom PSR-4 compatible autoloader.
 *
 * Supports multiple namespace prefixes and resolves classes to their corresponding
 * directory structures. Designed for environments without Composer.
 */
spl_autoload_register(function (string $class): void {
    /**
     * Namespace prefix mappings.
     * Keys are namespace prefixes; values are base directories (relative to this file).
     *
     * @var array<string, string>
     */
    $prefixes = [
        'App\\'    => __DIR__ . '/src/',
        'Config\\' => __DIR__ . '/config/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        // Does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($class, $prefix, $len) !== 0) {
            continue;
        }

        // Get the relative class name
        $relativeClass = substr($class, $len);

        // Replace namespace separators with directory separators
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
