<?php

declare(strict_types=1);

namespace ClassDiscovery\Infrastructure;

use ClassDiscovery\Application\Contracts\FileToFqcnResolver;
use ClassDiscovery\Domain\ValueObjects\DirectoryPath;
use ClassDiscovery\Domain\ValueObjects\NamespaceName;
use ClassDiscovery\Domain\ValueObjects\FullyQualifiedClassName;
use RuntimeException;

/**
 * Converts PHP file paths to fully qualified class names following PSR-4 rules.
 */
final class FileToFqcnResolverPsr4 implements FileToFqcnResolver
{
    public function resolve(
        DirectoryPath $baseDirectory,
        string $filePath,
        NamespaceName $baseNamespace
    ): FullyQualifiedClassName {
        $baseDir = $this->realPath($baseDirectory->value());
        $fileRealPath = $this->realPath($filePath);

        $this->assertFileWithinBaseDir($baseDir, $fileRealPath);

        $relative = $this->relativePath($baseDir, $fileRealPath);
        $className = $this->classNameFromPath($relative);

        return new FullyQualifiedClassName(
            $this->composeFqcn($baseNamespace, $className)
        );
    }

    private function realPath(string $path): string
    {
        $resolved = realpath($path);
        if ($resolved === false) {
            throw new RuntimeException("Could not resolve real path for '{$path}'.");
        }

        return rtrim($resolved, DIRECTORY_SEPARATOR);
    }

    private function assertFileWithinBaseDir(string $baseDir, string $fileRealPath): void
    {
        $base = $this->isWindows() ? strtoupper($baseDir) : $baseDir;
        $file = $this->isWindows() ? strtoupper($fileRealPath) : $fileRealPath;

        if (strpos($file, $base) === 0) {
            return;
        }

        throw new RuntimeException(
            "File path '{$fileRealPath}' is not within base directory '{$baseDir}'."
        );
    }

    private function relativePath(string $baseDir, string $fileRealPath): string
    {
        $relative = substr($fileRealPath, strlen($baseDir));
        return ltrim($relative, DIRECTORY_SEPARATOR);
    }

    private function classNameFromPath(string $relativePath): string
    {
        $classPath = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
        return (string) preg_replace('/\.php$/i', '', $classPath);
    }

    private function composeFqcn(NamespaceName $baseNamespace, string $className): string
    {
        return rtrim($baseNamespace->value(), '\\') . '\\' . $className;
    }

    private function isWindows(): bool
    {
        return stripos(PHP_OS, 'WIN') === 0;
    }
}
