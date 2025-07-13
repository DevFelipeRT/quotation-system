<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Tool;

use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateCacheInterface;
use Rendering\Domain\ValueObject\Shared\Directory;
use RuntimeException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Manages the file-based cache for pre-processed templates.
 *
 * This class is responsible for all interactions with the cache directory,
 * including generating cache paths, checking if a cached file is outdated
 * (stale), writing new content, and performing cache maintenance.
 */
final class TemplateCache implements TemplateCacheInterface
{
    /**
     * @param Directory $cacheDirectory A value object representing the validated cache directory.
     */
    public function __construct(private readonly Directory $cacheDirectory)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCompiledPath(string $sourcePath): string
    {
        $hash = sha1($sourcePath);
        
        return $this->cacheDirectory->path() . DIRECTORY_SEPARATOR . substr($hash, 0, 2) . DIRECTORY_SEPARATOR . substr($hash, 2, 2) . DIRECTORY_SEPARATOR . "{$hash}.php";
    }

    /**
     * {@inheritdoc}
     */
    public function isStale(string $sourcePath, string $compiledPath): bool
    {
        if (!is_file($compiledPath)) {
            return true;
        }

        return filemtime($sourcePath) > filemtime($compiledPath);
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $compiledPath, string $content): void
    {
        $directory = dirname($compiledPath);

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException(sprintf('Cache subdirectory "%s" could not be created.', $directory));
            }
        }
        
        $tmpPath = $directory . DIRECTORY_SEPARATOR . uniqid(basename($compiledPath), true);
        
        if (file_put_contents($tmpPath, $content) === false) {
            throw new RuntimeException(sprintf('Unable to write to temporary cache file: %s', $tmpPath));
        }

        if (rename($tmpPath, $compiledPath) === false) {
            @unlink($tmpPath);
            throw new RuntimeException(sprintf('Unable to rename temporary cache file to final destination: %s', $compiledPath));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cacheDirectory->path(), RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            if (!$action($fileinfo->getRealPath())) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function prune(int $lifetime = 2592000): int
    {
        $deletedCount = 0;
        $threshold = time() - $lifetime;

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cacheDirectory->path(), RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file->getMTime() < $threshold) {
                if (@unlink($file->getRealPath())) {
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }
}
