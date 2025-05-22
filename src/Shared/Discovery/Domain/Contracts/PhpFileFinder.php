<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Domain\Contracts;

use App\Shared\Discovery\Domain\ValueObjects\DirectoryPath;

interface PhpFileFinder
{
    /**
     * Finds all PHP files recursively under the given directory.
     *
     * @param DirectoryPath $directory
     * @return string[] List of absolute file paths to PHP files.
     */
    public function findAll(DirectoryPath $directory): array;
}
