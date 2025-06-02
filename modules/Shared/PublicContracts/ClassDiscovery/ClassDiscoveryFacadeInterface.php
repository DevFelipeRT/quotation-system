<?php

declare(strict_types=1);

namespace PublicContracts\ClassDiscovery;

interface ClassDiscoveryFacadeInterface
{
    /**
     * Discover all classes implementing a given interface in a namespace(optional).
     *
     * @param string $interfaceName The fully qualified name of the interface.
     * @param string|null $namespace The namespace to restrict the search to, or null for the default PSR-4 prefix.
     * @return string[]
     */
    public function implementing(string $interfaceName, ?string $namespace = null): array;

    /**
     * Discovers all concrete classes extending the given base class within a namespace(optional).
     *
     * @param string $className The fully qualified name of the class.
     * @param string|null $namespace The namespace to restrict the search to, or null for the default PSR-4 prefix.
     * @return string[]
     */
    public function extending(string $className, ?string $namespace = null): array;
}
