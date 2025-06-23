<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use Logging\Domain\Security\Contract\LogSecurityInterface;
use Logging\Domain\Exception\InvalidLogDirectoryException;
use RuntimeException;

/**
 * Immutable Value Object representing a secure log directory.
 *
 * Ensures validation, sanitization, and secure handling of directory paths,
 * utilizing domain-specific security and validation logic.
 * 
 * @immutable
 */
final class LogDirectory
{
    /**
     * @var string The validated and sanitized log directory path.
     */
    private string $path;

    /**
     * Constructs a LogDirectory instance using domain security facade.
     *
     * @param string                $path      The raw directory path.
     * @param LogSecurityInterface  $security  Domain security facade.
     *
     * @throws InvalidLogDirectoryException If directory path validation fails.
     */
    public function __construct(string $path, LogSecurityInterface $security)
    {
        $this->path = $this->validatePath($path, $security);
        $this->ensureExists();
        $this->ensureIsWritable();
    }

    /**
     * Retrieves the normalized directory path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
    
    /**
     * Checks if the directory path is writable.
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        return is_writable($this->path);
    }
    
    public function __toString(): string
    {
        return $this->path;
    }

    /**
     * Ensures the directory exists, creating it safely if necessary.
     *
     * @throws RuntimeException If the directory cannot be created.
     */
    private function ensureExists(): void
    {
        if (!is_dir($this->path)) {
            if (!@mkdir($this->path, 0770, true) && !is_dir($this->path)) {
                throw new RuntimeException("Failed to create log directory: {$this->path}");
            }
        }
    }

    /**
     * Ensures the directory exists, creating it safely if necessary.
     *
     * @throws RuntimeException If the directory cannot be created.
     */
    private function ensureIsWritable(): void
    {
        if (!is_writable($this->path)) {
            if (!@chmod($this->path, 0770)) {
                throw new RuntimeException("Log directory is not writable: {$this->path}");
            }
        }
    }
    
    /**
     * Validates the directory path using domain security facade.
     *
     * @param string               $path
     * @param LogSecurityInterface $security
     *
     * @return string
     *
     * @throws InvalidLogDirectoryException
     */
    private function validatePath(string $path, LogSecurityInterface $security): string
    {
        $sanitized = $security->sanitize($path) ?? '';

        if (empty($sanitized)) {
            throw new InvalidLogDirectoryException("Invalid log directory path provided: {$path}");
        }
        if ($sanitized !== $path) {
            throw new InvalidLogDirectoryException("Sanitized path does not match original: {$path}");
        }

        return $security->validateDirectory($sanitized);
    }
}
