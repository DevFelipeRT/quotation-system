<?php

declare(strict_types=1);

namespace ClassDiscovery\Application\Support;

use ClassDiscovery\Domain\ValueObjects\DirectoryPath;
use ClassDiscovery\Domain\ValueObjects\NamespaceName;
use ClassDiscovery\Domain\ValueObjects\FullyQualifiedClassName;
use ReflectionClass;
use Throwable;

trait ClassDiscoverySecurity
{
    /**
     * Determines if the given file path is a valid class file (starts with uppercase letter).
     */
    private function isClassFileName(string $filePath): bool
    {
        $filename = basename($filePath);
        return $filename !== '' && ctype_upper($filename[0]);
    }

    /**
     * Attempts to resolve the file to a FullyQualifiedClassName, or null on failure.
     */
    private function resolveFqcnSafely(
        $fileToFqcnResolver,
        DirectoryPath $baseDirectory,
        string $filePath,
        NamespaceName $baseNamespace
    ): ?FullyQualifiedClassName {
        try {
            return $fileToFqcnResolver->resolve($baseDirectory, $filePath, $baseNamespace);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Checks if a FQCN refers to a concrete, instantiable class (not interface, trait, or enum).
     */
    private function isConcreteClass(FullyQualifiedClassName $fqcn): bool
    {
        try {
            if (!class_exists($fqcn->value(), false)) {
                return false;
            }
            $reflection = new ReflectionClass($fqcn->value());
            if (
                $reflection->isInterface()
                || $reflection->isTrait()
                || (method_exists($reflection, 'isEnum') && $reflection->isEnum())
            ) {
                return false;
            }
            return $reflection->isInstantiable();
        } catch (Throwable $e) {
            return false;
        }
    }
}