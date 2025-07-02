<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing;

use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateProcessingServiceInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplatePathResolverInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateCacheInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateCompilerInterface;

/**
 * A high-level service that orchestrates the template compilation process.
 *
 * This class acts as a Facade for the template processing subsystem. Its main
 * responsibility is to take a template name, check its cache status,
 * trigger a re-compilation if necessary, and return the path to the final,
 * executable PHP script.
 */
final class TemplateProcessingService implements TemplateProcessingServiceInterface
{
    /**
     * @param TemplatePathResolverInterface $pathResolver The service for resolving template names to file paths.
     * @param TemplateCacheInterface $cache The cache manager for compiled templates.
     * @param TemplateCompilerInterface $compiler The engine that transforms template syntax into PHP.
     */
    public function __construct(
        private readonly TemplatePathResolverInterface $pathResolver,
        private readonly TemplateCacheInterface $cache,
        private readonly TemplateCompilerInterface $compiler
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $templateName): string
    {
        // 1. Delegate path resolution and validation.
        $sourcePath = $this->pathResolver->resolve($templateName);

        // 2. Get the expected path for the compiled file.
        $compiledPath = $this->cache->getCompiledPath($sourcePath);

        // 3. Delegate the cache check and re-compilation logic.
        $this->recompileIfStale($sourcePath, $compiledPath);

        // 4. Always return the path to the (now up-to-date) compiled file.
        return $compiledPath;
    }

    /**
     * Checks if a template is stale and recompiles it if necessary.
     *
     * This method encapsulates the logic of checking the cache, reading the
     * source file, compiling the content, and writing the result to the cache.
     *
     * @param string $sourcePath The absolute path to the original source file.
     * @param string $compiledPath The absolute path to the cached, compiled file.
     */
    private function recompileIfStale(string $sourcePath, string $compiledPath): void
    {
        if ($this->cache->isStale($sourcePath, $compiledPath)) {
            $content = file_get_contents($sourcePath);
            $compiledContent = $this->compiler->compile($content);
            $this->cache->write($compiledPath, $compiledContent);
        }
    }
}