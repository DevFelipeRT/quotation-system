<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Support;

use RuntimeException;

/**
 * NamespacePathResolver
 *
 * Dynamically resolves a PSR-4-compliant namespace to a directory path,
 * based on a single root namespace → base path mapping.
 */
final class NamespacePathResolver
{
    /**
     * @var string
     */
    private static string $rootNamespace = 'App';

    /**
     * @var string
     */
    private static string $rootDirectory = BASE_PATH . '/src';

    /**
     * Resolves a PSR-4 namespace to a directory path assuming a base prefix mapping.
     *
     * @param string $namespace
     * @return string
     * @throws RuntimeException
     */
    public static function resolve(string $namespace): string
    {
        if (!str_starts_with($namespace, self::$rootNamespace)) {
            throw new RuntimeException("Namespace '{$namespace}' does not start with the configured root namespace '" . self::$rootNamespace . "'.");
        }

        $relative = substr($namespace, strlen(self::$rootNamespace));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, ltrim($relative, '\\'));

        $absolutePath = rtrim(self::$rootDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativePath;

        if (!is_dir($absolutePath)) {
            throw new RuntimeException("Resolved path '{$absolutePath}' does not exist or is not a directory.");
        }

        return $absolutePath;
    }

    /**
     * Optional: configure root namespace and directory
     */
    public static function configure(string $rootNamespace, string $rootDirectory): void
    {
        self::$rootNamespace = trim($rootNamespace, '\\');
        self::$rootDirectory = rtrim($rootDirectory, DIRECTORY_SEPARATOR);
    }
}
