<?php

declare(strict_types=1);

namespace ClassDiscovery\Application\Service;

use ClassDiscovery\Application\Contracts\ClassDiscoveryServiceInterface;
use ClassDiscovery\Domain\FqcnCollection;
use ClassDiscovery\Domain\ValueObjects\FullyQualifiedClassName;
use ClassDiscovery\Domain\ValueObjects\InterfaceName;
use ClassDiscovery\Domain\ValueObjects\NamespaceName;

use PublicContracts\ClassDiscovery\ClassDiscoveryFacadeInterface;


final class ClassDiscoveryFacade implements 
    ClassDiscoveryFacadeInterface,
    ClassDiscoveryServiceInterface
{
    private ClassDiscoveryService $scanner;

    public function __construct(ClassDiscoveryService $scanner)
    {
        $this->scanner = $scanner;
    }

    /**
     * Discovers all FQCNs implementing a given interface.
     *
     * @param string $interfaceName The fully qualified name of the interface.
     * @param string|null $namespace The namespace to restrict the search to, or null for the default PSR-4 prefix.
     * @return string[]
     */
    public function implementing(string $interfaceName, ?string $namespace = null): array
    {
        $interfaceVO = new InterfaceName($interfaceName);
        $namespaceVO = is_null($namespace) ? null : new NamespaceName($namespace);

        try {
            $collection = $this->discoverImplementing($interfaceVO, $namespaceVO);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }

        return $this->toArrayOfValues($collection);
    }

    /**
     * Discovers all FQCNs extending a given class.
     *
     * @param string $className The fully qualified name of the class.
     * @param string|null $namespace The namespace to restrict the search to, or null for the default PSR-4 prefix.
     * @return string[]
     */
    public function extending(string $className, ?string $namespace = null): array
    {
        $fqcnVO = new FullyQualifiedClassName($className);
        $namespaceVO = is_null($namespace) ? null : new NamespaceName($namespace);

        try {
            $collection = $this->discoverExtending($fqcnVO, $namespaceVO);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }

        return $this->toArrayOfValues($collection);
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

    private function toArrayOfValues(FqcnCollection $collection): array
    {
        return array_map(
            fn(FullyQualifiedClassName $fqcn) => $fqcn->value(),
            $collection->toArray()
        );
    }
}