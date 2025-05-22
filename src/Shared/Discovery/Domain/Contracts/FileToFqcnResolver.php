<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Domain\Contracts;

use App\Shared\Discovery\Domain\ValueObjects\DirectoryPath;
use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;
use App\Shared\Discovery\Domain\ValueObjects\FullyQualifiedClassName;

interface FileToFqcnResolver
{
    /**
     * Resolves a PHP file path to its Fully Qualified Class Name (FQCN),
     * given a base directory and namespace context.
     *
     * @param DirectoryPath $baseDirectory
     * @param string $filePath Absolute path to the PHP file
     * @param NamespaceName $baseNamespace
     * @return FullyQualifiedClassName
     */
    public function resolve(
        DirectoryPath $baseDirectory,
        string $filePath,
        NamespaceName $baseNamespace
    ): FullyQualifiedClassName;
}
