<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Infrastructure;

use App\Shared\Discovery\Domain\Contracts\NamespaceToDirectoryResolver;
use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;
use App\Shared\Discovery\Domain\ValueObjects\DirectoryPath;
use InvalidArgumentException;

final class NamespaceToDirectoryResolverPsr4 implements NamespaceToDirectoryResolver
{
    private string $psr4Prefix;
    private string $basePath;

    /**
     * @param string $psr4Prefix Exemplo: 'App'
     * @param string $basePath Caminho absoluto, ex: '/caminho/para/src'
     */
    public function __construct(string $psr4Prefix, string $basePath)
    {
        if (trim($psr4Prefix) === '') {
            throw new InvalidArgumentException('PSR-4 prefix cannot be empty.');
        }
        if (!is_dir($basePath)) {
            throw new InvalidArgumentException("Base path does not exist: '{$basePath}'.");
        }
        $this->psr4Prefix = trim($psr4Prefix, '\\');
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    public function resolve(NamespaceName $namespace): DirectoryPath
    {
        $namespaceValueObjects = $namespace->value();
        if (strpos($namespaceValueObjects, $this->psr4Prefix) !== 0) {
            throw new InvalidArgumentException("Namespace '{$namespaceValueObjects}' does not start with PSR-4 prefix '{$this->psr4Prefix}'.");
        }

        $relativeNamespace = substr($namespaceValueObjects, strlen($this->psr4Prefix));
        $relativeNamespace = ltrim($relativeNamespace, '\\');
        $relativePath = $relativeNamespace !== ''
            ? str_replace('\\', DIRECTORY_SEPARATOR, $relativeNamespace)
            : '';

        $fullPath = $this->basePath . ($relativePath !== '' ? DIRECTORY_SEPARATOR . $relativePath : '');

        if (!is_dir($fullPath)) {
            throw new InvalidArgumentException("Resolved directory does not exist: '{$fullPath}'.");
        }

        return new DirectoryPath($fullPath);
    }
}
