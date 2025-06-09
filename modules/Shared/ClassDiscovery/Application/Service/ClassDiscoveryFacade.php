<?php

declare(strict_types=1);

namespace ClassDiscovery\Application\Service;

use ClassDiscovery\Domain\ValueObjects\FullyQualifiedClassName;
use ClassDiscovery\Domain\ValueObjects\InterfaceName;
use ClassDiscovery\Domain\ValueObjects\NamespaceName;
use ClassDiscovery\Domain\FqcnCollection;

use PublicContracts\ClassDiscovery\ClassDiscoveryFacadeInterface;

final class ClassDiscoveryFacade implements ClassDiscoveryFacadeInterface
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
            $collection = $this->scanner->discoverImplementing($interfaceVO, $namespaceVO);
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
            $collection = $this->scanner->discoverExtending($fqcnVO, $namespaceVO);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }

        return $this->toArrayOfValues($collection);
    }

    /**
     * Discover all concrete classes in the specified namespace.
     * 
     * @param string|null $namespace The namespace to execute the search, or null for the default PSR-4 prefix.
     * @return string[]
     */
    public function inNamespace(
        ?string $namespace = null
    ): array {
        $namespaceVO = is_null($namespace) ? null : new NamespaceName($namespace);

        try {
            $collection = $this->scanner->discoverInNamespace($namespaceVO);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }

        return $this->toArrayOfValues($collection);
    }

    private function toArrayOfValues(FqcnCollection $collection): array
    {
        return array_map(
            fn(FullyQualifiedClassName $fqcn) => $fqcn->value(),
            $collection->toArray()
        );
    }
}