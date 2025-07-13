<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\TemplateProcessing;

use RuntimeException;

/**
 * Defines the contract for a file-based cache manager for templates.
 *
 * This interface abstracts the underlying filesystem operations required for
 * caching compiled templates. It provides a standard API for generating cache
 * paths, checking for stale files, and writing content to the cache.
 */
interface TemplateCacheInterface
{
    /**
     * Generates a unique and deterministic path for a compiled file in the cache.
     *
     * @param string $sourcePath The absolute path of the original source file.
     * @return string The absolute path for the compiled file in the cache.
     */
    public function getCompiledPath(string $sourcePath): string;

    /**
     * Checks if a cached file is "stale" and needs to be re-compiled.
     *
     * A file is considered stale if it does not exist in the cache or if the
     * source file has been modified more recently than the cached file.
     *
     * @param string $sourcePath The path to the original source file.
     * @param string $compiledPath The path to the cached, compiled file.
     * @return bool True if the file needs to be re-compiled, false otherwise.
     */
    public function isStale(string $sourcePath, string $compiledPath): bool;

    /**
     * Writes compiled content to a specified path in the cache.
     *
     * @param string $compiledPath The full path where the file will be saved.
     * @param string $content The content to be written to the file.
     * @throws RuntimeException if the file cannot be written.
     */
    public function write(string $compiledPath, string $content): void;

    /**
     * Removes all files and subdirectories from the cache directory.
     *
     * This method is useful for clearing the entire cache, for instance, during
     * a new deployment or for troubleshooting purposes.
     *
     * @return bool True on success, false on failure.
     */
    public function clear(): bool;

    /**
     * Removes all cache files older than a specified lifetime.
     *
     * This method can be used in a scheduled task (cron job) to prevent the
     * cache directory from growing indefinitely with stale, unused files.
     *
     * @param int $lifetime The maximum age of a file in seconds. Defaults to 30 days.
     * @return int The number of files deleted.
     */
    public function prune(int $lifetime = 2592000): int;
}