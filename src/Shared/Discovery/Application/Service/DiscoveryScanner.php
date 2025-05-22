<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Application\Service;

use App\Shared\Discovery\Domain\ValueObjects\InterfaceName;
use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;
use App\Shared\Discovery\Domain\ValueObjects\DirectoryPath;
use App\Shared\Discovery\Domain\ValueObjects\FullyQualifiedClassName;
use App\Shared\Discovery\Domain\Collection\FullyQualifiedClassNameCollection;
use App\Shared\Discovery\Domain\Contracts\NamespaceToDirectoryResolver;
use App\Shared\Discovery\Domain\Contracts\FileToFqcnResolver;
use App\Shared\Discovery\Domain\Contracts\PhpFileFinder;
use ReflectionClass;
use Throwable;

final class DiscoveryScanner
{
    private NamespaceToDirectoryResolver $namespaceToDirectoryResolver;
    private FileToFqcnResolver $fileToFqcnResolver;
    private PhpFileFinder $phpFileFinder;

    public function __construct(
        NamespaceToDirectoryResolver $namespaceToDirectoryResolver,
        FileToFqcnResolver $fileToFqcnResolver,
        PhpFileFinder $phpFileFinder
    ) {
        $this->namespaceToDirectoryResolver = $namespaceToDirectoryResolver;
        $this->fileToFqcnResolver = $fileToFqcnResolver;
        $this->phpFileFinder = $phpFileFinder;
    }

    public function discoverImplementing(
        InterfaceName $interface,
        NamespaceName $namespace
    ): FullyQualifiedClassNameCollection {
        $directory = $this->namespaceToDirectoryResolver->resolve($namespace);
        $phpFiles = $this->phpFileFinder->findAll($directory);

        $implementations = [];

        foreach ($phpFiles as $filePath) {
            $fqcn = $this->resolveFqcnSafely($directory, $filePath, $namespace);
            if ($fqcn === null) {
                continue;
            }
            if (!$this->isInstantiable($fqcn)) {
                continue;
            }
            if (!$this->implementsInterface($fqcn, $interface)) {
                continue;
            }
            $implementations[] = $fqcn;
        }

        return new FullyQualifiedClassNameCollection($implementations);
    }

    private function resolveFqcnSafely(
        DirectoryPath $baseDirectory,
        string $filePath,
        NamespaceName $baseNamespace
    ): ?FullyQualifiedClassName {
        try {
            return $this->fileToFqcnResolver->resolve($baseDirectory, $filePath, $baseNamespace);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function isInstantiable(FullyQualifiedClassName $fqcn): bool
    {
        if (!class_exists($fqcn->value())) {
            return false;
        }
        try {
            $reflection = new ReflectionClass($fqcn->value());
            return $reflection->isInstantiable();
        } catch (Throwable $e) {
            return false;
        }
    }

    private function implementsInterface(
        FullyQualifiedClassName $fqcn,
        InterfaceName $interface
    ): bool {
        try {
            $reflection = new ReflectionClass($fqcn->value());
            return $reflection->implementsInterface($interface->value());
        } catch (Throwable $e) {
            return false;
        }
    }
}
