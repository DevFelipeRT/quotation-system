<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing;

use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\CompilingServiceInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateProcessingServiceInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplatePathResolverInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateCacheInterface;

/**
 * A high-level service that orchestrates the template compilation process.
 *
 * This class acts as a Facade for the template processing subsystem. Its main
 * responsibility is to take a template name, check its cache status,
 * trigger a re-compilation if necessary, and return the path to the final,
 * executable PHP script. It supports a debug mode for development environments.
 */
final class TemplateProcessingService implements TemplateProcessingServiceInterface
{
    /**
     * @param TemplatePathResolverInterface $pathResolver The service for resolving template names to file paths.
     * @param TemplateCacheInterface $cache The cache manager for compiled templates.
     * @param CompilingServiceInterface $compiler The service responsible for compiling template content.
     * @param bool $debugMode When true, templates are recompiled on every request.
     */
    public function __construct(
        private readonly TemplatePathResolverInterface $pathResolver,
        private readonly TemplateCacheInterface $cache,
        private readonly CompilingServiceInterface $compiler,
        private readonly bool $debugMode = false
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $templateName): string
    {
        $sourcePath = $this->pathResolver->resolve($templateName);
        $compiledPath = $this->cache->getCompiledPath($sourcePath);

        $this->recompileIfStale($sourcePath, $compiledPath);

        return $compiledPath;
    }

    /**
     * Checks if a template is stale and recompiles it if necessary.
     *
     * In debug mode, this method will force recompilation on every call.
     * Otherwise, it relies on the cache's stale check.
     *
     * @param string $sourcePath The absolute path to the original source file.
     * @param string $compiledPath The absolute path to the cached, compiled file.
     */
    private function recompileIfStale(string $sourcePath, string $compiledPath): void
    {
        if ($this->debugMode || $this->cache->isStale($sourcePath, $compiledPath)) {
            $content = file_get_contents($sourcePath);
            $compiledContent = $this->compiler->compile($content);
            $this->cache->write($compiledPath, $compiledContent);
        }
    }
}
