<?php

declare(strict_types=1);

namespace Discovery\Application\Service;

use Discovery\Application\Contracts\DiscoveryScannerInterface;
use Discovery\Application\Contracts\ScannerFacadeInterface;
use Discovery\Domain\Collection\FqcnCollection;
use Discovery\Application\Service\DiscoveryScanner;
use Discovery\Domain\ValueObjects\FullyQualifiedClassName;
use Discovery\Domain\ValueObjects\InterfaceName;
use Discovery\Domain\ValueObjects\NamespaceName;

final class DiscoveryScannerFacade implements 
    ScannerFacadeInterface,
    DiscoveryScannerInterface
{
    private DiscoveryScanner $scanner;

    public function __construct(DiscoveryScanner $scanner)
    {
        $this->scanner = $scanner;
    }

    /**
     * Discovers all FQCNs implementing a given interface.
     *
     * @param string $interfaceName The fully qualified name of the interface.
     * @param string|null $namespace The namespace to restrict the search to, or null for the default PSR-4 prefix.
     * @return FqcnCollection
     */
    public function implementing(string $interfaceName, ?string $namespace = null): FqcnCollection
    {
        $interfaceVO = new InterfaceName($interfaceName);
        $namespaceVO = is_null($namespace) ? null : new NamespaceName($namespace);

        try {
            return $this->discoverImplementing($interfaceVO, $namespaceVO);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * Discovers all FQCNs extending a given class.
     *
     * @param string $className The fully qualified name of the class.
     * @param string|null $namespace The namespace to restrict the search to, or null for the default PSR-4 prefix.
     * @return FqcnCollection
     */
    public function extending(string $className, ?string $namespace = null): FqcnCollection
    {
        $fqcnVO = new FullyQualifiedClassName($className);
        $namespaceVO = is_null($namespace) ? null : new NamespaceName($namespace);

        try {
            return $this->discoverExtending($fqcnVO, $namespaceVO);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * Discovers all FQCNs implementing a given interface.
     *
     * @param InterfaceName $interfaceName The fully qualified name of the interface.
     * @param NamespaceName|null $namespace The namespace to restrict the search to, or null for the default PSR-4 prefix.
     * @return FqcnCollection
     */
    public function discoverImplementing(
        InterfaceName $interfaceName, 
        ?NamespaceName $namespace = null
    ): FqcnCollection
    {
        try {
            return $this->scanner->discoverImplementing($interfaceName, $namespace);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * Discovers all FQCNs extending a given class.
     *
     * @param FullyQualifiedClassName $className The fully qualified name of the class.
     * @param NamespaceName|null $namespace The namespace to restrict the search to, or null for the default PSR-4 prefix.
     * @return FqcnCollection
     */
    public function discoverExtending(
        FullyQualifiedClassName $className, 
        ?NamespaceName $namespace = null
    ): FqcnCollection
    {
        try {
            return $this->scanner->discoverExtending($className, $namespace);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
    }
}