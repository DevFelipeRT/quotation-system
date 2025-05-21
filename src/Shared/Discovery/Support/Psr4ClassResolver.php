<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Support;

use RuntimeException;

/**
 * Psr4ClassResolver
 *
 * Resolves a fully qualified class name (FQCN) from a file path,
 * assuming PSR-4 compliance and a known namespace prefix.
 */
final class Psr4ClassResolver
{
    /**
     * Converts a PHP file path within a base directory to a FQCN.
     *
     * Example:
     *   Base directory: /var/www/src/Kernel/Adapters/EventListening/Providers
     *   Namespace:      App\Kernel\Adapters\EventListening\Providers
     *   File path:      /var/www/src/Kernel/Adapters/EventListening/Providers/Admin/MyProvider.php
     *   Result:         App\Kernel\Adapters\EventListening\Providers\Admin\MyProvider
     *
     * @param string $baseDir Absolute path that maps to $namespacePrefix
     * @param string $filePath Absolute path to the .php file inside $baseDir
     * @param string $namespacePrefix PSR-4 namespace prefix for $baseDir
     * @return string Fully qualified class name (FQCN)
     */
    public static function resolve(string $baseDir, string $filePath, string $namespacePrefix): string
    {
        if (!str_starts_with($filePath, $baseDir)) {
            throw new RuntimeException("File path '{$filePath}' is not within base directory '{$baseDir}'.");
        }

        $relativePath = substr($filePath, strlen($baseDir));
        $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);

        $classPath = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
        $className = preg_replace('/\.php$/', '', $classPath);

        return rtrim($namespacePrefix, '\\') . '\\' . $className;
    }
}