<?php

declare(strict_types=1);

namespace Rendering\Domain\Shared\ValueObject;

use Rendering\Domain\Contract\SecurityServiceInterface;

/**
 * Represents a directory path as an immutable Value Object.
 *
 * It uses an injected service during construction to ensure the path is valid
 * and secure. Once created, a Directory object is guaranteed to represent a
 * safe and existing directory.
 */
final class Directory
{
    /**
     * @var string The normalized, validated directory path.
     */
    private readonly string $path;

    /**
     * @param string $path The raw path to the directory.
     * @param SecurityServiceInterface $securityService The service used to validate the path.
     */
    public function __construct(string $path, SecurityServiceInterface $securityService)
    {
        $securityService->validateDirectoryPath($path);
        $this->path = rtrim($path, '/');
    }

    /**
     * Returns the normalized directory path string.
     *
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Allows the object to be cast to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->path;
    }
}