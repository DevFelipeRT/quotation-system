<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Application\Contracts;

use App\Shared\Discovery\Domain\ValueObjects\InterfaceName;
use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;
use App\Shared\Discovery\Domain\Collection\FqcnCollection;

interface DiscoveryScannerInterface
{
    /**
     * Discover all classes implementing a given interface in a namespace.
     *
     * @param InterfaceName $interface
     * @param NamespaceName $namespace
     * @return FqcnCollection
     */
    public function discoverImplementing(
        InterfaceName $interface,
        NamespaceName $namespace
    ): FqcnCollection;
}
