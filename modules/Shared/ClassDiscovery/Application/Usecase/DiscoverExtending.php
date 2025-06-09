<?php

declare(strict_types=1);

namespace ClassDiscovery\Application\UseCase;

use ClassDiscovery\Application\Contracts\NamespaceToDirectoryResolver;
use ClassDiscovery\Application\Contracts\FileToFqcnResolver;
use ClassDiscovery\Application\Contracts\PhpFileFinder;
use ClassDiscovery\Application\Support\ClassDiscoverySecurity;
use ClassDiscovery\Domain\ValueObjects\FullyQualifiedClassName;
use ClassDiscovery\Domain\ValueObjects\NamespaceName;
use ClassDiscovery\Domain\FqcnCollection;
use ReflectionClass;
use Throwable;

/**
 * Use case for discovering all concrete subclasses of a given base class
 * within a specified namespace.
 *
 * This use case performs all discovery steps internally and should be used directly by application services.
 */
final class DiscoverExtending
{
    use ClassDiscoverySecurity;

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
     * Discovers all concrete subclasses of the specified base class within the given namespace.
     *
     * @param FullyQualifiedClassName $baseClass   The base class for which to search subclasses.
     * @param NamespaceName|null $namespace        Optional namespace scope. If null, the root namespace is used.
     * @return FqcnCollection                      Strongly-typed collection of fully-qualified class names.
     */
    public function execute(
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
            $fqcn = $this->resolveFqcnSafely($this->fileToFqcnResolver, $directory, $filePath, $effectiveNamespace);
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