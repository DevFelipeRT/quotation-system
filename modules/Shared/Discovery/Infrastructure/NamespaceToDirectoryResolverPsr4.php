<?php

declare(strict_types=1);

namespace Discovery\Infrastructure;

use Discovery\Domain\Contracts\NamespaceToDirectoryResolver;
use Discovery\Domain\ValueObjects\NamespaceName;
use Discovery\Domain\ValueObjects\DirectoryPath;
use InvalidArgumentException;

/**
 * Resolves namespaces to filesystem directories according to PSR-4 conventions.
 * 
 * Maps a root namespace (PSR-4 prefix) to a base directory and resolves
 * any namespace under this prefix to its corresponding directory.
 *
 * Performs strict validation of prefix syntax, directory existence, readability,
 * and prevents directory traversal outside the configured base path.
 */
final class NamespaceToDirectoryResolverPsr4 implements NamespaceToDirectoryResolver
{
    /**
     * @var string The PSR-4 namespace prefix (root namespace), e.g. 'App'
     */
    private string $psr4Prefix;

    /**
     * @var string The absolute path to the root directory for this namespace prefix
     */
    private string $basePath;

    /**
     * @var NamespaceName Cached value object for the root namespace
     */
    private NamespaceName $rootNamespace;

    /**
     * @param string $psr4Prefix  The PSR-4 prefix (e.g. 'App')
     * @param string $basePath    Absolute path to base directory (e.g. '/var/www/src')
     *
     * @throws InvalidArgumentException If prefix syntax is invalid or directory does not exist.
     */
    public function __construct(string $psr4Prefix, string $basePath)
    {
        $psr4Prefix = trim($psr4Prefix, '\\');
        if ($psr4Prefix === '') {
            throw new InvalidArgumentException('PSR-4 prefix cannot be empty.');
        }
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $psr4Prefix)) {
            throw new InvalidArgumentException("Invalid PSR-4 prefix syntax: '{$psr4Prefix}'.");
        }
        if (!is_dir($basePath)) {
            throw new InvalidArgumentException("Base path does not exist: '{$basePath}'.");
        }
        $this->psr4Prefix = $psr4Prefix;
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->rootNamespace = new NamespaceName($this->psr4Prefix);
    }

    /**
     * Resolves the provided namespace to a corresponding directory on disk.
     *
     * @param NamespaceName $namespace
     * @return DirectoryPath
     * 
     * @throws InvalidArgumentException If namespace does not match prefix,
     *                                  directory does not exist/readable,
     *                                  or path traversal is detected.
     */
    public function resolve(NamespaceName $namespace): DirectoryPath
    {
        $namespaceString = $namespace->value();
        $this->assertStartsWithPrefix($namespaceString);

        $relativePath = $this->toRelativePath($namespaceString);
        $fullPath = $this->composeFullPath($relativePath);

        $this->assertDirectoryExistsAndReadable($fullPath);
        $this->assertPathWithinBase($fullPath);

        return new DirectoryPath($fullPath);
    }

    /**
     * Returns the root namespace handled by this resolver.
     *
     * @return NamespaceName
     */
    public function getRootNamespace(): NamespaceName
    {
        return $this->rootNamespace;
    }

    /**
     * Asserts that the provided namespace string starts with the configured prefix.
     *
     * @param string $namespaceString
     * @throws InvalidArgumentException
     */
    private function assertStartsWithPrefix(string $namespaceString): void
    {
        if (strpos($namespaceString, $this->psr4Prefix) !== 0) {
            throw new InvalidArgumentException(
                "Namespace '{$namespaceString}' does not start with PSR-4 prefix '{$this->psr4Prefix}'."
            );
        }
    }

    /**
     * Converts the namespace string to a relative filesystem path (may be empty).
     *
     * @param string $namespaceString
     * @return string
     */
    private function toRelativePath(string $namespaceString): string
    {
        $relativeNamespace = substr($namespaceString, strlen($this->psr4Prefix));
        $relativeNamespace = ltrim($relativeNamespace, '\\');
        return $relativeNamespace !== ''
            ? str_replace('\\', DIRECTORY_SEPARATOR, $relativeNamespace)
            : '';
    }

    /**
     * Composes the absolute filesystem path for the given relative path.
     *
     * @param string $relativePath
     * @return string
     */
    private function composeFullPath(string $relativePath): string
    {
        return $this->basePath . ($relativePath !== '' ? DIRECTORY_SEPARATOR . $relativePath : '');
    }

    /**
     * Asserts that the given directory exists and is readable.
     *
     * @param string $directoryPath
     * @throws InvalidArgumentException
     */
    private function assertDirectoryExistsAndReadable(string $directoryPath): void
    {
        if (!is_dir($directoryPath)) {
            throw new InvalidArgumentException("Resolved directory does not exist: '{$directoryPath}'.");
        }
        if (!is_readable($directoryPath)) {
            throw new InvalidArgumentException("Resolved directory is not readable: '{$directoryPath}'.");
        }
    }

    /**
     * Asserts that the resolved path does not escape the configured base path (prevents path traversal).
     *
     * @param string $fullPath
     * @throws InvalidArgumentException
     */
    private function assertPathWithinBase(string $fullPath): void
    {
        $base = realpath($this->basePath);
        $target = realpath($fullPath);

        if ($base === false || $target === false) {
            throw new InvalidArgumentException("Could not resolve real path for validation.");
        }
        // Ensure $target starts with $base (and is not outside)
        if (strpos($target, $base) !== 0) {
            throw new InvalidArgumentException(
                "Resolved directory '{$fullPath}' is outside of the base path '{$this->basePath}'."
            );
        }
    }
}
