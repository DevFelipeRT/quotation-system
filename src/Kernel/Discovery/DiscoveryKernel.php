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
use App\Shared\Discovery\Domain\ValueObjects\FullyQualifiedClassName;

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

    public function __construct(?string $psr4Prefix = null, ?string $baseSourceDir = null)
    {
        $this->psr4Prefix = trim($psr4Prefix ?? PSR4_PREFIX, '\\');
        var_dump($this->psr4Prefix);
        $this->baseSourceDir = rtrim($baseSourceDir ?? SRC_DIR, DIRECTORY_SEPARATOR);
        var_dump($this->baseSourceDir);
    }

    public function scanner(): DiscoveryScanner
    {
        if ($this->scanner === null) {
            $this->scanner = $this->createScanner();
        }
        return $this->scanner;
    }

    /**
     * Discovers all FQCNs implementing a given interface.
     * Searches the specified namespace or uses the PSR-4 prefix
     */
    public function discoverImplementing(string $interfaceName, ?string $namespace = null): FqcnCollection
    {
        $namespace = $namespace ?? ($this->psr4Prefix);
        $scanner = $this->scanner();
        $interfaceVO = new InterfaceName($interfaceName);
        echo 'Discovering implementations of: ' . $interfaceVO->value() . PHP_EOL;
        $namespaceVO = new NamespaceName($namespace);
        var_dump($namespaceVO);
        echo 'Searching in namespace: ' . $namespaceVO->value() . PHP_EOL;

        try {
            $result = $scanner->discoverImplementing($interfaceVO, $namespaceVO);
            var_dump($result);
        } catch (\InvalidArgumentException $e) {
            echo 'Error: ' . $e->getMessage() . PHP_EOL;
            throw $e;
        }
        
        return $result;
    }

    public function discoverExtending(string $relativeClass): FqcnCollection
    {
        $scanner = $this->scanner();
        $fqcnVO = new FullyQualifiedClassName($relativeClass);
        echo 'Discovering extensions of: ' . $fqcnVO->value() . PHP_EOL;

        try {
            $result = $scanner->discoverExtending($fqcnVO);
            var_dump($result);
        } catch (\InvalidArgumentException $e) {
            echo 'Error: ' . $e->getMessage() . PHP_EOL;
            throw $e;
        }

        return $result;
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
}
