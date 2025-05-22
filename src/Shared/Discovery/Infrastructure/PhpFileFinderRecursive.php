<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Infrastructure;

use App\Shared\Discovery\Domain\Contracts\PhpFileFinder;
use App\Shared\Discovery\Domain\ValueObjects\DirectoryPath;

final class PhpFileFinderRecursive implements PhpFileFinder
{
    /**
     * @param DirectoryPath $directory
     * @return string[] List of absolute file paths to PHP files.
     */
    public function findAll(DirectoryPath $directory): array
    {
        $phpFiles = [];
        $path = $directory->value();

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $phpFiles[] = $file->getRealPath();
            }
        }

        return $phpFiles;
    }
}
