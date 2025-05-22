<?php

declare(strict_types=1);

namespace App\Application\Extension;

use App\Shared\Discovery\Application\Service\DiscoveryScanner;
use App\Shared\Discovery\Domain\ValueObjects\InterfaceName;
use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;
use App\Shared\Discovery\Domain\Collection\FqcnCollection;

/**
 * Serviço de descoberta de extensões (plugins/modificadores do sistema).
 *
 * Responsabilidade única: localizar as classes que implementam uma interface específica em um namespace de extensões.
 */
final class ExtensionDiscoveryService
{
    private DiscoveryScanner $scanner;

    public function __construct(DiscoveryScanner $scanner)
    {
        $this->scanner = $scanner;
    }

    /**
     * Descobre FQCNs de todas as extensões (implementações) de uma interface no namespace informado.
     *
     * @param string $extensionInterfaceName FQCN da interface de extensão (ex: 'App\Extensions\ExtensionInterface')
     * @param string $extensionsNamespace Namespace onde estão as implementações (ex: 'App\Extensions')
     * @return FqcnCollection Coleção de FQCNs de extensões encontradas.
     * @throws \InvalidArgumentException Se argumentos são inválidos ou não existem.
     */
    public function discoverExtensions(
        string $extensionInterfaceName,
        string $extensionsNamespace
    ): FqcnCollection {
        $interfaceVO = new InterfaceName($extensionInterfaceName);
        $namespaceVO = new NamespaceName($extensionsNamespace);

        return $this->scanner->discoverImplementing($interfaceVO, $namespaceVO);
    }
}
