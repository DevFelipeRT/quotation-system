<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use Logging\Domain\Exception\InvalidLogDirectoryException;
use RuntimeException;

/**
 * Value Object representing a secure log directory.
 *
 * - Strong validation and sanitization.
 * - Path traversal, null bytes, and root directory protections.
 */
final class LogDirectory
{
    /**
     * @var string
     */
    private string $path;

    /**
     * Creates a validated, secure LogDirectory value object.
     *
     * @param string $path Absolute or relative path to the log directory.
     * @throws InvalidLogDirectoryException If the path is invalid, unsafe, or empty.
     */
    public function __construct(string $path)
    {
        $sanitized = $this->sanitizePath($path);

        if ($this->isEmptyPath($sanitized)) {
            throw InvalidLogDirectoryException::empty();
        }
        if ($this->isRootPath($sanitized)) {
            throw InvalidLogDirectoryException::unsafe('Path must not be system root.');
        }
        if (!$this->isSafePath($sanitized)) {
            throw InvalidLogDirectoryException::unsafe("Path contains invalid or dangerous characters: {$sanitized}");
        }
        $this->path = $this->normalizePath($sanitized);
    }

    /**
     * Returns the absolute directory path as a string.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Ensures that the directory exists, creating it if necessary.
     *
     * @throws RuntimeException If the directory cannot be created.
     */
    public function ensureExists(): void
    {
        if (!is_dir($this->path)) {
            if (!$this->safeMkdir($this->path, 0770)) {
                throw new RuntimeException("Failed to create log directory: {$this->path}");
            }
        }
    }

    /**
     * Verifies that the directory is writable.
     */
    public function isWritable(): bool
    {
        return is_writable($this->path);
    }

    public function __toString(): string
    {
        return $this->path;
    }

    // --- Private Security and Validation Methods ---

    /**
     * Removes null bytes and trims the path.
     */
    private function sanitizePath(string $path): string
    {
        return rtrim(str_replace("\0", '', trim($path)), "/\\");
    }

    /**
     * Appends a directory separator if not present.
     */
    private function normalizePath(string $path): string
    {
        return $path . DIRECTORY_SEPARATOR;
    }

    private function isEmptyPath(string $path): bool
    {
        return $path === '';
    }

    private function isRootPath(string $path): bool
    {
        // Unix root or Windows root (like C:\)
        return $path === DIRECTORY_SEPARATOR || preg_match('#^[a-zA-Z]:\\\\$#', $path) === 1;
    }

    private function isSafePath(string $path): bool
    {
        // Forbid parent directory traversal and suspicious characters
        return !preg_match('#(\.\.|[\x00]|[<>:"|?*])#', $path);
    }

    private function safeMkdir(string $dir, int $mode): bool
    {
        return @mkdir($dir, $mode, true) || is_dir($dir);
    }
}
