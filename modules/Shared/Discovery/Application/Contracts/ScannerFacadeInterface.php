<?php

declare(strict_types=1);

namespace Discovery\Application\Contracts;

use Discovery\Domain\ValueObjects\InterfaceName;
use Discovery\Domain\ValueObjects\NamespaceName;
use Discovery\Domain\Collection\FqcnCollection;
use Discovery\Domain\ValueObjects\FullyQualifiedClassName;

interface ScannerFacadeInterface extends DiscoveryScannerInterface
{
    /**
     * Discover all classes implementing a given interface in a namespace(optional).
     *
     * @param InterfaceName $interface
     * @param NamespaceName $namespace
     * @return FqcnCollection
     */
    public function discoverImplementing(
        InterfaceName $interface,
        ?NamespaceName $namespace = null
    ): FqcnCollection;

    /**
     * Discovers all concrete classes extending the given base class within a namespace(optional).
     *
     * @param FullyQualifiedClassName $baseClass   The base (abstract or concrete) class.
     * @param NamespaceName $namespace             The namespace in which to search.
     * @return FqcnCollection                      Collection of subclasses found.
     */
    public function discoverExtending(
        FullyQualifiedClassName $baseClass,
        ?NamespaceName $namespace = null
    ): FqcnCollection;

    /**
     * Discover all classes implementing a given interface in a namespace(optional).
     *
     * @param string $interfaceName The fully qualified name of the interface.
     * @param string|null $namespace The namespace to restrict the search to, or null for the default PSR-4 prefix.
     * @return FqcnCollection
     */
    public function implementing(string $interfaceName, ?string $namespace = null): FqcnCollection;

    /**
     * Discovers all concrete classes extending the given base class within a namespace(optional).
     *
     * @param string $className The fully qualified name of the class.
     * @param string|null $namespace The namespace to restrict the search to, or null for the default PSR-4 prefix.
     * @return FqcnCollection
     */
    public function extending(string $className, ?string $namespace = null): FqcnCollection;
}
