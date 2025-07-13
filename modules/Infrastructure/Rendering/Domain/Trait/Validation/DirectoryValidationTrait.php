<?php

declare(strict_types=1);

namespace Rendering\Domain\Trait\Validation;

use Rendering\Domain\Exception\InvalidDirectoryException;

trait DirectoryValidationTrait
{
    /**
     * {@inheritdoc}
     */
    public function validateDirectoryPath(string $path): void
    {
        // 1. Check for empty or whitespace-only paths.
        if (trim($path) === '') {
            throw new InvalidDirectoryException('Directory path cannot be empty.');
        }
        
        // 2. Basic security check for directory traversal patterns in the raw path.
        if (str_contains($path, '..')) {
            throw new InvalidDirectoryException('Invalid directory path: traversal characters are not permitted.');
        }

        // 3. Check if the path exists and is actually a directory.
        if (!is_dir($path)) {
            throw new InvalidDirectoryException("Path provided is not a valid directory: {$path}");
        }

        // 4. Check if the directory is readable.
        if (!is_readable($path)) {
            throw new InvalidDirectoryException("Directory is not readable: {$path}");
        }
    }
}