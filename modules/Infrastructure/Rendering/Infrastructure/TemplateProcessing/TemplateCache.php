<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing;

use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateCacheInterface;
use Rendering\Domain\Shared\ValueObject\Directory;
use RuntimeException;

/**
 * Manages the file-based cache for pre-processed templates.
 *
 * This class is responsible for all interactions with the cache directory,
 * including generating cache paths, checking if a cached file is outdated
 * (stale), and writing new content to the cache.
 */
final class TemplateCache implements TemplateCacheInterface
{
    /**
     * @param Directory $cacheDirectory A value object representing the validated cache directory.
     */
    public function __construct(private readonly Directory $cacheDirectory)
    {}

    /**
     * {@inheritdoc}
     */
    public function getCompiledPath(string $sourcePath): string
    {
        $hash = sha1($sourcePath);
        
        // Use the Directory object as a string to get its path.
        return "{$this->cacheDirectory}/" . substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . "/{$hash}.php";
    }

    /**
     * {@inheritdoc}
     */
    public function isStale(string $sourcePath, string $compiledPath): bool
    {
        return !is_file($compiledPath) || filemtime($sourcePath) > filemtime($compiledPath);
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $compiledPath, string $content): void
    {
        $directory = dirname($compiledPath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException(sprintf('Cache subdirectory "%s" could not be created', $directory));
            }
        }

        if (file_put_contents($compiledPath, $content) === false) {
             throw new RuntimeException(sprintf('Failed to write to cache file: %s', $compiledPath));
        }
    }
}