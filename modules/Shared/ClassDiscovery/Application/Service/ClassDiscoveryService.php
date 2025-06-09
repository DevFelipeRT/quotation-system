<?php

declare(strict_types=1);

namespace ClassDiscovery\Application\Service;

use ClassDiscovery\Application\Contracts\NamespaceToDirectoryResolver;
use ClassDiscovery\Application\Contracts\FileToFqcnResolver;
use ClassDiscovery\Application\Contracts\PhpFileFinder;
use ClassDiscovery\Application\UseCase\DiscoverImplementing;
use ClassDiscovery\Application\UseCase\DiscoverExtending;
use ClassDiscovery\Application\UseCase\DiscoverInNamespace;
use ClassDiscovery\Domain\ValueObjects\InterfaceName;
use ClassDiscovery\Domain\ValueObjects\FullyQualifiedClassName;
use ClassDiscovery\Domain\ValueObjects\NamespaceName;
use ClassDiscovery\Domain\FqcnCollection;

/**
 * Application service responsible for class discovery operations.
 * Acts as the main entry point for use by controllers, facades, or external modules.
 */
final class ClassDiscoveryService
{
    private DiscoverImplementing $discoverImplementing;
    private DiscoverExtending $discoverExtending;
    private DiscoverInNamespace $discoverInNamespace;

    /**
     * Constructor.
     *
     * @param NamespaceToDirectoryResolver $namespaceToDirectoryResolver
     * @param FileToFqcnResolver $fileToFqcnResolver
     * @param PhpFileFinder $phpFileFinder
     */
    public function __construct(
        NamespaceToDirectoryResolver $namespaceToDirectoryResolver,
        FileToFqcnResolver $fileToFqcnResolver,
        PhpFileFinder $phpFileFinder
    ) {
        $this->discoverImplementing = new DiscoverImplementing(
            $namespaceToDirectoryResolver,
            $fileToFqcnResolver,
            $phpFileFinder
        );
        $this->discoverExtending = new DiscoverExtending(
            $namespaceToDirectoryResolver,
            $fileToFqcnResolver,
            $phpFileFinder
        );
        $this->discoverInNamespace = new DiscoverInNamespace(
            $namespaceToDirectoryResolver,
            $fileToFqcnResolver,
            $phpFileFinder
        );
    }

    /**
     * Discover all concrete classes implementing the given interface in the specified namespace.
     */
    public function discoverImplementing(
        InterfaceName $interface,
        ?NamespaceName $namespace = null
    ): FqcnCollection {
        return $this->discoverImplementing->execute($interface, $namespace);
    }

    /**
     * Discover all concrete subclasses of the given base class in the specified namespace.
     */
    public function discoverExtending(
        FullyQualifiedClassName $baseClass,
        ?NamespaceName $namespace = null
    ): FqcnCollection {
        return $this->discoverExtending->execute($baseClass, $namespace);
    }

    /**
     * Discover all concrete classes in the specified namespace.
     */
    public function discoverInNamespace(
        ?NamespaceName $namespace = null
    ): FqcnCollection {
        return $this->discoverInNamespace->execute($namespace);
    }
}
