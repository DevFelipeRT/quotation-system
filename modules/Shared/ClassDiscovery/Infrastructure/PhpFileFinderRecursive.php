<?php

declare(strict_types=1);

namespace ClassDiscovery\Infrastructure;

use ClassDiscovery\Application\Contracts\PhpFileFinder;
use ClassDiscovery\Domain\ValueObjects\DirectoryPath;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PhpFileFinderRecursive implements PhpFileFinder
{
    /**
     * @param DirectoryPath $directory
     * @return string[] List of absolute file paths to PHP files.
     */
    public function findAll(DirectoryPath $directory): array
    {
        $path = $directory->value();
        $iterator = $this->iterator($path);
        $phpFiles = $this->iteratePhpFiles($iterator);

        return $phpFiles;
    }

    private function iterator(string $path): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );
    }

    private function iteratePhpFiles(RecursiveIteratorIterator $iterator): array
    {
        $phpFiles = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $phpFiles[] = $file->getRealPath();
            }
        }

        return $phpFiles;
    }
}
