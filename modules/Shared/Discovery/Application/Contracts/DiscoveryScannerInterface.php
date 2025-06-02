<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Application\Contracts;

use App\Shared\Discovery\Domain\ValueObjects\InterfaceName;
use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;
use App\Shared\Discovery\Domain\Collection\FqcnCollection;
use App\Shared\Discovery\Domain\ValueObjects\FullyQualifiedClassName;

interface DiscoveryScannerInterface
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
}
