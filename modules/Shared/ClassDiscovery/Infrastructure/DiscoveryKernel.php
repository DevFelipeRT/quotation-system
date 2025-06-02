<?php

declare(strict_types=1);

namespace ClassDiscovery\Infrastructure;

use ClassDiscovery\Application\Contracts\ClassDiscoveryServiceInterface;
use ClassDiscovery\Application\Service\ClassDiscoveryFacade;
use ClassDiscovery\Application\Service\ClassDiscoveryService;
use ClassDiscovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use ClassDiscovery\Infrastructure\FileToFqcnResolverPsr4;
use ClassDiscovery\Infrastructure\PhpFileFinderRecursive;

use PublicContracts\ClassDiscovery\ClassDiscoveryFacadeInterface;

/**
 * DiscoveryKernel
 *
 * Configures and orchestrates the ClassDiscoveryService for extension/plugin discovery,
 * supporting targeted search with optional project-wide fallback.
 */
final class DiscoveryKernel
{
    private string $psr4Prefix;
    private string $baseSourceDir;
    private ?ClassDiscoveryService $scanner = null;
    private ?ClassDiscoveryFacade $discoveryClassDiscoveryFacade = null;

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
     * @return ClassDiscoveryServiceInterface
     */
    public function scanner(): ClassDiscoveryServiceInterface
    {
        if ($this->scanner === null) {
            $this->boot(false);
        }
        return $this->scanner;
    }

    /**
     * @return ClassDiscoveryFacadeInterface
     */
    public function facade(): ClassDiscoveryFacadeInterface
    {
        if ($this->discoveryClassDiscoveryFacade === null) {
            $this->boot(true);
        }
        return $this->discoveryClassDiscoveryFacade;
    }

    /**
     * @return ClassDiscoveryServiceInterface
     */
    public function customScanner(
        string $prefix, 
        string $sourceDir
    ): ClassDiscoveryServiceInterface
    {
        $scanner = $this->createScanner($prefix, $sourceDir);
        return $scanner;
    }

    /**
     * @return ClassDiscoveryServiceInterface
     */
    public function customFacade(
        string $prefix, 
        string $sourceDir
    ): ClassDiscoveryServiceInterface
    {
        $scanner = $this->createScanner($prefix, $sourceDir);
        return $this->createFacade($scanner);
    }

    /**
     * Internal boot method to initialize the scanner and optionally the facade.
     *
     * @param bool $facade Whether to create a ClassDiscoveryFacade instance.
     */
    private function boot(bool $facade = true): void
    {
        $this->scanner = $this->createScanner();
        if ($facade) {
            $this->discoveryClassDiscoveryFacade = $this->createFacade();
        }
    }

    /**
     * Internal helper to create a ClassDiscoveryService instance.
     *
     * @return ClassDiscoveryService
     */
    private function createScanner(?string $prefix = null, ?string $sourceDir = null): ClassDiscoveryServiceInterface
    {
        return new ClassDiscoveryService(
            new NamespaceToDirectoryResolverPsr4($prefix ?? $this->psr4Prefix, $sourceDir ?? $this->baseSourceDir),
            new FileToFqcnResolverPsr4(),
            new PhpFileFinderRecursive()
        );
    }

    /**
     * Internal helper to create a ClassDiscoveryFacade instance.
     *
     * @return ClassDiscoveryFacade
     */
    private function createFacade(?ClassDiscoveryService $scanner = null): ClassDiscoveryFacadeInterface
    {
        return new ClassDiscoveryFacade($scanner ?? $this->scanner);
    }
}
