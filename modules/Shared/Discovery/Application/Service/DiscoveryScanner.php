<?php

declare(strict_types=1);

namespace Discovery\Application\Service;

use Discovery\Application\Contracts\DiscoveryScannerInterface;
use Discovery\Domain\ValueObjects\InterfaceName;
use Discovery\Domain\ValueObjects\NamespaceName;
use Discovery\Domain\ValueObjects\DirectoryPath;
use Discovery\Domain\ValueObjects\FullyQualifiedClassName;
use Discovery\Domain\Collection\FqcnCollection;
use Discovery\Domain\Contracts\NamespaceToDirectoryResolver;
use Discovery\Domain\Contracts\FileToFqcnResolver;
use Discovery\Domain\Contracts\PhpFileFinder;
use ReflectionClass;
use Throwable;

/**
 * Service for runtime discovery of PHP classes by interface implementation or inheritance.
 *
 * Resolves a namespace to its PSR-4 directory using the provided resolver.
 * Scans all PHP files recursively under the directory.
 * Filters out files whose names do not start with an uppercase letter (project class convention).
 * Attempts to resolve each file to a FullyQualifiedClassName safely.
 * Checks if the class is concrete, instantiable, and either implements an interface or extends a base class.
 * Returns a strongly-typed collection of FQCNs.
 *
 * Any file or class failing a check is silently skipped.
 */
final class DiscoveryScanner implements DiscoveryScannerInterface
{
    private NamespaceToDirectoryResolver $namespaceToDirectoryResolver;
    private FileToFqcnResolver $fileToFqcnResolver;
    private PhpFileFinder $phpFileFinder;

    /**
     * @param NamespaceToDirectoryResolver $namespaceToDirectoryResolver
     * @param FileToFqcnResolver $fileToFqcnResolver
     * @param PhpFileFinder $phpFileFinder
     */
    public function __construct(
        NamespaceToDirectoryResolver $namespaceToDirectoryResolver,
        FileToFqcnResolver $fileToFqcnResolver,
        PhpFileFinder $phpFileFinder
    ) {
        $this->namespaceToDirectoryResolver = $namespaceToDirectoryResolver;
        $this->fileToFqcnResolver = $fileToFqcnResolver;
        $this->phpFileFinder = $phpFileFinder;
    }

    /**
     * Discovers all concrete classes implementing a given interface within a namespace.
     * If no namespace is given, defaults to the root namespace handled by the resolver.
     *
     * @param InterfaceName $interface
     * @param NamespaceName|null $namespace
     * @return FqcnCollection
     */
    public function discoverImplementing(
        InterfaceName $interface,
        ?NamespaceName $namespace = null
    ): FqcnCollection {
        $effectiveNamespace = $namespace ?? $this->namespaceToDirectoryResolver->getRootNamespace();
        $directory = $this->namespaceToDirectoryResolver->resolve($effectiveNamespace);
        $phpFiles = $this->phpFileFinder->findAll($directory);

        $implementations = [];

        foreach ($phpFiles as $filePath) {
            if (!$this->isClassFileName($filePath)) {
                continue;
            }
            $fqcn = $this->resolveFqcnSafely($directory, $filePath, $effectiveNamespace);
            if ($fqcn === null) {
                continue;
            }
            if (!$this->isConcreteClass($fqcn)) {
                continue;
            }
            if (!$this->implementsInterface($fqcn, $interface)) {
                continue;
            }
            $implementations[] = $fqcn;
        }

        return new FqcnCollection($implementations);
    }

    /**
     * Discovers all concrete subclasses of a given base class within a namespace.
     * If no namespace is given, defaults to the root namespace handled by the resolver.
     *
     * @param FullyQualifiedClassName $baseClass
     * @param NamespaceName|null $namespace
     * @return FqcnCollection
     */
    public function discoverExtending(
        FullyQualifiedClassName $baseClass,
        ?NamespaceName $namespace = null
    ): FqcnCollection {
        $effectiveNamespace = $namespace ?? $this->namespaceToDirectoryResolver->getRootNamespace();
        $directory = $this->namespaceToDirectoryResolver->resolve($effectiveNamespace);
        $phpFiles = $this->phpFileFinder->findAll($directory);

        $subclasses = [];

        foreach ($phpFiles as $filePath) {
            if (!$this->isClassFileName($filePath)) {
                continue;
            }
            $fqcn = $this->resolveFqcnSafely($directory, $filePath, $effectiveNamespace);
            if ($fqcn === null) {
                continue;
            }
            if (!$this->isConcreteClass($fqcn)) {
                continue;
            }
            if ($fqcn->value() === $baseClass->value()) {
                continue; // Exclude the base class itself
            }
            if ($this->isSubclassOf($fqcn, $baseClass)) {
                $subclasses[] = $fqcn;
            }
        }

        return new FqcnCollection($subclasses);
    }

    /**
     * Determines if the given file path is a valid class file (starts with uppercase letter).
     *
     * @param string $filePath
     * @return bool
     */
    private function isClassFileName(string $filePath): bool
    {
        $filename = basename($filePath);
        return $filename !== '' && ctype_upper($filename[0]);
    }

    /**
     * Attempts to resolve the file to a FullyQualifiedClassName, or null on failure.
     *
     * @param DirectoryPath $baseDirectory
     * @param string $filePath
     * @param NamespaceName $baseNamespace
     * @return FullyQualifiedClassName|null
     */
    private function resolveFqcnSafely(
        DirectoryPath $baseDirectory,
        string $filePath,
        NamespaceName $baseNamespace
    ): ?FullyQualifiedClassName {
        try {
            return $this->fileToFqcnResolver->resolve($baseDirectory, $filePath, $baseNamespace);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Checks if a FQCN refers to a concrete, instantiable class (not interface, trait, or enum).
     *
     * @param FullyQualifiedClassName $fqcn
     * @return bool
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

    /**
     * Determines if the FQCN implements the specified interface.
     *
     * @param FullyQualifiedClassName $fqcn
     * @param InterfaceName $interface
     * @return bool
     */
    private function implementsInterface(
        FullyQualifiedClassName $fqcn,
        InterfaceName $interface
    ): bool {
        try {
            $reflection = new ReflectionClass($fqcn->value());
            return $reflection->implementsInterface($interface->value());
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Determines if the FQCN is a subclass of the specified base class.
     *
     * @param FullyQualifiedClassName $fqcn
     * @param FullyQualifiedClassName $baseClass
     * @return bool
     */
    private function isSubclassOf(
        FullyQualifiedClassName $fqcn,
        FullyQualifiedClassName $baseClass
    ): bool {
        try {
            $reflection = new ReflectionClass($fqcn->value());
            return $reflection->isSubclassOf($baseClass->value());
        } catch (Throwable $e) {
            return false;
        }
    }
}
