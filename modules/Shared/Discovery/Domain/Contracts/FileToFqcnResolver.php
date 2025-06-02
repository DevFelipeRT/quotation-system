<?php

declare(strict_types=1);

namespace Discovery\Domain\Contracts;

use Discovery\Domain\ValueObjects\DirectoryPath;
use Discovery\Domain\ValueObjects\NamespaceName;
use Discovery\Domain\ValueObjects\FullyQualifiedClassName;

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
