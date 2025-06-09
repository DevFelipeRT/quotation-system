<?php

declare(strict_types=1);

namespace ClassDiscovery\Domain\ValueObjects;

final class DirectoryPath
{
    private string $value;

    /**
     * @param string $directoryPath
     * @throws \InvalidArgumentException If the path does not exist or is not a directory.
     */
    public function __construct(string $directoryPath)
    {
        $this->value = $this->validateDirectoryPath($directoryPath);
    }

    /**
     * Returns the absolute directory path as string.
     */
    public function value(): string
    {
        return $this->value;
    }

    private function validateDirectoryPath(string $directoryPath): string
    {
        $trimmed = trim($directoryPath);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Directory path cannot be empty.');
        }
        if (!is_dir($trimmed)) {
            throw new \InvalidArgumentException("Directory does not exist: '{$directoryPath}'.");
        }
        return $trimmed;
    }
}
