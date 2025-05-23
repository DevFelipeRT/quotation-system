<?php

declare(strict_types=1);

namespace App\Kernel\Discovery;

use App\Shared\Discovery\Application\Service\DiscoveryScanner;
use App\Shared\Discovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use App\Shared\Discovery\Infrastructure\FileToFqcnResolverPsr4;
use App\Shared\Discovery\Infrastructure\PhpFileFinderRecursive;
use App\Shared\Discovery\Domain\ValueObjects\InterfaceName;
use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;
use App\Shared\Discovery\Domain\Collection\FqcnCollection;

/**
 * DiscoveryKernel
 *
 * Configures and orchestrates the DiscoveryScanner for extension/plugin discovery,
 * supporting targeted search with optional project-wide fallback.
 */
final class DiscoveryKernel
{
    private string $psr4Prefix;
    private string $baseSourceDir;
    private ?DiscoveryScanner $scanner = null;

    public function __construct(string $psr4Prefix, string $baseSourceDir)
    {
        $this->psr4Prefix = trim($psr4Prefix, '\\');
        $this->baseSourceDir = rtrim($baseSourceDir, DIRECTORY_SEPARATOR);
    }

    public function scanner(): DiscoveryScanner
    {
        if ($this->scanner === null) {
            $this->scanner = $this->createScanner();
        }
        return $this->scanner;
    }

    /**
     * Discovers extension FQCNs (classes implementing ExtensionInterface)
     * in the given namespace or, if enabled, with fallback logic.
     *
     * @param string|null $namespace         Namespace to search (optional).
     * @param bool        $fallbackToProject If true, applies fallback search logic.
     * @return FqcnCollection
     */
    public function discoverExtensions(?string $namespace = null, bool $fallbackToProject = false): FqcnCollection
    {
        $interfaceName = $this->psr4Prefix . '\\Extensions\\ExtensionInterface';
        $namespacesToScan = $this->buildNamespacesToScan($namespace, $fallbackToProject);
        return $this->findFirstNamespaceWithImplementations($interfaceName, $namespacesToScan);
    }

    /**
     * Discovers all FQCNs implementing a given interface in a namespace.
     */
    public function discoverImplementations(string $interfaceName, ?string $namespace = null): FqcnCollection
    {
        $namespace = $namespace ?? ($this->psr4Prefix);
        $scanner = $this->scanner();
        $interfaceVO = new InterfaceName($interfaceName);
        $namespaceVO = new NamespaceName($namespace);
        return $scanner->discoverImplementing($interfaceVO, $namespaceVO);
    }

    // ---------- Private helpers (real SRP, justified decomposition) ----------

    private function createScanner(): DiscoveryScanner
    {
        return new DiscoveryScanner(
            new NamespaceToDirectoryResolverPsr4($this->psr4Prefix, $this->baseSourceDir),
            new FileToFqcnResolverPsr4(),
            new PhpFileFinderRecursive()
        );
    }

    /**
     * Builds the search namespaces respecting fallback order.
     */
    private function buildNamespacesToScan(?string $namespace, bool $fallback): array
    {
        $namespaces = [];
        $primary = $namespace ?? ($this->psr4Prefix . '\\Extensions');
        $this->appendIfMissing($namespaces, $primary);
        if ($fallback) {
            $this->appendIfMissing($namespaces, $this->psr4Prefix . '\\Extensions');
            $this->appendIfMissing($namespaces, $this->psr4Prefix);
        }
        return $namespaces;
    }

    /**
     * Adds $namespace to $list if not already present.
     */
    private function appendIfMissing(array &$list, string $namespace): void
    {
        if (!in_array($namespace, $list, true)) {
            $list[] = $namespace;
        }
    }

    /**
     * Tries each namespace, returning the first non-empty implementation set.
     */
    private function findFirstNamespaceWithImplementations(string $interfaceName, array $namespaces): FqcnCollection
    {
        foreach ($namespaces as $namespace) {
            try {
                $result = $this->discoverImplementations($interfaceName, $namespace);
                if (!$result->isEmpty()) {
                    return $result;
                }
            } catch (\InvalidArgumentException $e) {
                // Directory for namespace does not exist. Continue to next.
                continue;
            }
        }
        return new FqcnCollection([]);
    }
}
