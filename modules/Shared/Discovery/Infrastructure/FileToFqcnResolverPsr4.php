<?php

declare(strict_types=1);

namespace Discovery\Infrastructure;

use Discovery\Domain\Contracts\FileToFqcnResolver;
use Discovery\Domain\ValueObjects\DirectoryPath;
use Discovery\Domain\ValueObjects\NamespaceName;
use Discovery\Domain\ValueObjects\FullyQualifiedClassName;
use RuntimeException;

final class FileToFqcnResolverPsr4 implements FileToFqcnResolver
{
    public function resolve(
        DirectoryPath $baseDirectory,
        string $filePath,
        NamespaceName $baseNamespace
    ): FullyQualifiedClassName {
        $baseDir = realpath($baseDirectory->value());
        $fileRealPath = realpath($filePath);

        if ($baseDir === false || $fileRealPath === false) {
            throw new RuntimeException("Could not resolve real path for base directory or file: '{$baseDirectory->value()}', '{$filePath}'");
        }

        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);

        // Windows: comparison is case-insensitive
        if (stripos(PHP_OS, 'WIN') === 0) {
            $baseDirCmp = strtoupper($baseDir);
            $fileRealCmp = strtoupper($fileRealPath);
            if (strpos($fileRealCmp, $baseDirCmp) !== 0) {
                throw new RuntimeException("File path '{$fileRealPath}' is not within base directory '{$baseDir}'.");
            }
        } else {
            if (strpos($fileRealPath, $baseDir) !== 0) {
                throw new RuntimeException("File path '{$fileRealPath}' is not within base directory '{$baseDir}'.");
            }
        }

        $relativePath = substr($fileRealPath, strlen($baseDir));
        $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);

        $classPath = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

        $className = preg_replace('/\.php$/i', '', $classPath);

        $fqcn = rtrim($baseNamespace->value(), '\\') . '\\' . $className;

        return new FullyQualifiedClassName($fqcn);
    }
}
