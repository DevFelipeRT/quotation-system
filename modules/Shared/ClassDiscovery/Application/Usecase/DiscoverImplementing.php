<?php

declare(strict_types=1);

namespace ClassDiscovery\Application\UseCase;

use ClassDiscovery\Application\Contracts\NamespaceToDirectoryResolver;
use ClassDiscovery\Application\Contracts\FileToFqcnResolver;
use ClassDiscovery\Application\Contracts\PhpFileFinder;
use ClassDiscovery\Application\Support\ClassDiscoverySecurity;
use ClassDiscovery\Domain\ValueObjects\InterfaceName;
use ClassDiscovery\Domain\ValueObjects\NamespaceName;
use ClassDiscovery\Domain\ValueObjects\FullyQualifiedClassName;
use ClassDiscovery\Domain\FqcnCollection;
use ReflectionClass;
use Throwable;

/**
 * Use case for discovering all concrete classes implementing a given interface
 * within a specified namespace.
 *
 * This use case performs all steps internally and is suitable for direct use by application services.
 */
final class DiscoverImplementing
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
     * Discovers all concrete classes implementing a given interface within a namespace.
     *
     * @param InterfaceName $interface
     * @param NamespaceName|null $namespace
     * @return FqcnCollection
     */
    public function execute(
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
            $fqcn = $this->resolveFqcnSafely($this->fileToFqcnResolver, $directory, $filePath, $effectiveNamespace);
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
}