<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Infrastructure;

use App\Shared\Discovery\Application\Contracts\DiscoveryScannerInterface;
use App\Shared\Discovery\Application\Contracts\ScannerFacadeInterface;
use App\Shared\Discovery\Application\Service\DiscoveryScannerFacade;
use App\Shared\Discovery\Application\Service\DiscoveryScanner;
use App\Shared\Discovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use App\Shared\Discovery\Infrastructure\FileToFqcnResolverPsr4;
use App\Shared\Discovery\Infrastructure\PhpFileFinderRecursive;

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
    private ?DiscoveryScannerFacade $discoveryScannerFacade = null;

    /**
     * @param string $psr4Prefix    The PSR-4 namespace prefix to search within.
     * @param string $baseSourceDir The base directory corresponding to the PSR-4 namespace prefix.
     */
    public function __construct(string $psr4Prefix, string $baseSourceDir)
    {
        $this->psr4Prefix = trim($psr4Prefix, '\\');
        $this->baseSourceDir = rtrim($baseSourceDir, DIRECTORY_SEPARATOR);
    }

    /**
     * @return DiscoveryScannerInterface
     */
    public function scanner(): DiscoveryScannerInterface
    {
        if ($this->scanner === null) {
            $this->boot(false);
        }
        return $this->scanner;
    }

    /**
     * @return ScannerFacadeInterface
     */
    public function facade(): ScannerFacadeInterface
    {
        if ($this->discoveryScannerFacade === null) {
            $this->boot(true);
        }
        return $this->discoveryScannerFacade;
    }

    /**
     * @return DiscoveryScannerInterface
     */
    public function customScanner(
        string $prefix, 
        string $sourceDir
    ): DiscoveryScannerInterface
    {
        $scanner = $this->createScanner($prefix, $sourceDir);
        return $scanner;
    }

    /**
     * @return DiscoveryScannerInterface
     */
    public function customFacade(
        string $prefix, 
        string $sourceDir
    ): DiscoveryScannerInterface
    {
        $scanner = $this->createScanner($prefix, $sourceDir);
        return $this->createFacade($scanner);
    }

    /**
     * Internal boot method to initialize the scanner and optionally the facade.
     *
     * @param bool $facade Whether to create a DiscoveryScannerFacade instance.
     */
    private function boot(bool $facade = true): void
    {
        $this->scanner = $this->createScanner();
        if ($facade) {
            $this->discoveryScannerFacade = $this->createFacade();
        }
    }

    /**
     * Internal helper to create a DiscoveryScanner instance.
     *
     * @return DiscoveryScanner
     */
    private function createScanner(?string $prefix = null, ?string $sourceDir = null): DiscoveryScannerInterface
    {
        return new DiscoveryScanner(
            new NamespaceToDirectoryResolverPsr4($prefix ?? $this->psr4Prefix, $sourceDir ?? $this->baseSourceDir),
            new FileToFqcnResolverPsr4(),
            new PhpFileFinderRecursive()
        );
    }

    /**
     * Internal helper to create a DiscoveryScannerFacade instance.
     *
     * @return DiscoveryScannerFacade
     */
    private function createFacade(?DiscoveryScanner $scanner = null): ScannerFacadeInterface
    {
        return new DiscoveryScannerFacade($scanner ?? $this->scanner);
    }
}
