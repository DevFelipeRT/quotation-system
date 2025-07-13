<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component;

use Rendering\Infrastructure\Contract\RenderingEngine\Component\ComponentRenderingServiceInterface;
use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\ComponentRendererInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\TemplateEngineInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateProcessingServiceInterface;
use Rendering\Infrastructure\RenderingEngine\Api\ViewApi;
use RuntimeException;

/**
 * Orchestrates the rendering process by dispatching tasks to specialized renderers.
 *
 * This service uses a dispatch map for O(1) complexity, providing high performance
 * by avoiding iteration. It receives a map where keys are renderable class/interface names
 * and values are the corresponding component renderer instances.
 */
final class ComponentRenderingService implements ComponentRenderingServiceInterface
{
    /**
     * @param array<class-string, ComponentRendererInterface> $renderers The dispatch map of specialist renderers.
     * @param TemplateEngineInterface $engine The low-level template execution engine.
     * @param TemplateProcessingServiceInterface $templateProcessor The service for resolving template paths.
     */
    public function __construct(
        private readonly array $renderers,
        private readonly TemplateEngineInterface $engine,
        private readonly TemplateProcessingServiceInterface $templateProcessor
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function render(RenderableInterface $renderable): string
    {
        $renderer = $this->findRendererFor($renderable);
        return $renderer->render($renderable, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function renderTemplateFile(string $templateFile, array $data, ?RenderableInterface $parentContext): string
    {
        $templateData = $this->buildTemplateDataForFile($data, $parentContext);
        $compiledPath = $this->templateProcessor->resolve($templateFile);
        $output = $this->engine->execute($compiledPath, $templateData);
        
        return $output;
    }

    /**
     * Finds the appropriate component renderer for the given renderable object from the dispatch map.
     *
     * It first attempts a direct match on the object's concrete class, then checks
     * for implemented interfaces for broader matching.
     */
    private function findRendererFor(RenderableInterface $renderable): ComponentRendererInterface
    {
        $renderableClass = get_class($renderable);

        // Attempt a direct match with the concrete class name first for performance.
        if (isset($this->renderers[$renderableClass])) {
            return $this->renderers[$renderableClass];
        }

        // If no direct match, check against registered interfaces.
        foreach (class_implements($renderableClass) as $interface) {
            if (isset($this->renderers[$interface])) {
                return $this->renderers[$interface];
            }
        }

        throw new RuntimeException(
            sprintf('No component renderer registered for renderable class or interface "%s".', $renderableClass)
        );
    }

    /**
     * Prepares the data array for an included template file.
     *
     * This method creates a new ViewApi instance for the included template,
     * allowing it to have its own context while inheriting the parent's
     * ability to render further partials.
     */
    private function buildTemplateDataForFile(array $data, ?RenderableInterface $parentContext): array
    {
        $viewApi = new ViewApi($this, $parentContext);
        
        $templateData = $data;
        $templateData['view'] = $viewApi;
        
        return $templateData;
    }
}
