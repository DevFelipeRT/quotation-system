<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Domain\Contracts;

use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;
use App\Shared\Discovery\Domain\ValueObjects\DirectoryPath;

interface NamespaceToDirectoryResolver
{
    /**
     * Resolves a namespace to its corresponding directory path.
     *
     * @param NamespaceName $namespace
     * @return DirectoryPath
     */
    public function resolve(NamespaceName $namespace): DirectoryPath;

    /**
     * Returns the root namespace handled by this resolver.
     *
     * @return NamespaceName
     */
    public function getRootNamespace(): NamespaceName;

}
