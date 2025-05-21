<?php

declare(strict_types=1);

namespace App\Shared\Discovery;

use App\Shared\Discovery\Support\Psr4ClassResolver;
use App\Shared\Discovery\Support\NamespacePathResolver;
use ReflectionClass;
use Throwable;

/**
 * DiscoveryScanner
 *
 * Scans a PSR-4-compliant namespace by resolving its directory structure,
 * and discovers classes that implement a given interface.
 */
final class DiscoveryScanner
{
    /**
     * Discovers and instantiates all classes under the resolved namespace that implement the specified interface.
     *
     * @template T of object
     * @param class-string<T> $interface
     * @param string $namespace Fully qualified namespace to scan
     * @return list<T>
     */
    public function discoverImplementing(string $interface, string $namespace): array
    {
        $baseDir = NamespacePathResolver::resolve($namespace);

        $results = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            try {
                $fqcn = Psr4ClassResolver::resolve($baseDir, $file->getRealPath(), $namespace);
                if (!class_exists($fqcn)) {
                    continue;
                }

                $ref = new ReflectionClass($fqcn);

                if (!$ref->isInstantiable() || !$ref->implementsInterface($interface)) {
                    continue;
                }

                $results[] = new $fqcn();
            } catch (Throwable $e) {
                continue;
            }
        }

        return $results;
    }
}