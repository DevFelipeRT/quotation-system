<?php

declare(strict_types=1);

namespace ClassDiscovery\Application\UseCase;

use ClassDiscovery\Application\Contracts\NamespaceToDirectoryResolver;
use ClassDiscovery\Application\Contracts\FileToFqcnResolver;
use ClassDiscovery\Application\Contracts\PhpFileFinder;
use ClassDiscovery\Application\Support\ClassDiscoverySecurity;
use ClassDiscovery\Domain\ValueObjects\NamespaceName;
use ClassDiscovery\Domain\FqcnCollection;

/**
 * Use case for discovering all concrete classes within a specified namespace.
 *
 * This use case performs all steps internally and should be used directly by application services.
 */
final class DiscoverInNamespace
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
     * Discovers all concrete classes within the specified namespace.
     *
     * @param NamespaceName|null $namespace   Optional namespace scope. If null, the root namespace is used.
     * @return FqcnCollection                Strongly-typed collection of fully-qualified class names.
     */
    public function execute(
        ?NamespaceName $namespace = null
    ): FqcnCollection {
        $effectiveNamespace = $namespace ?? $this->namespaceToDirectoryResolver->getRootNamespace();
        $directory = $this->namespaceToDirectoryResolver->resolve($effectiveNamespace);
        $phpFiles = $this->phpFileFinder->findAll($directory);

        $concreteClasses = [];

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
            $concreteClasses[] = $fqcn;
        }

        return new FqcnCollection($concreteClasses);
    }
}