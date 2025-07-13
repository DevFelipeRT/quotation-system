<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component;

use Rendering\Domain\Contract\Page\PageInterface;
use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\ComponentRendererInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\ContextBuilderInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\TemplateEngineInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateProcessingServiceInterface;
use Rendering\Infrastructure\RenderingEngine\Component\Renderer\PageRenderer;
use Rendering\Infrastructure\RenderingEngine\Component\Renderer\PartialRenderer;
use Rendering\Infrastructure\RenderingEngine\Component\Renderer\ViewRenderer;
use RuntimeException;

/**
 * Factory responsible for creating a fully configured ComponentRenderingService.
 *
 * This class encapsulates the logic of assembling the dispatch map of specialized
 * renderers, keeping the application's bootstrap logic clean and focused.
 */
final class ComponentRenderingServiceFactory
{
    /**
     * The static configuration map that associates a domain component interface
     * with its corresponding specialist renderer class.
     *
     * @var array<class-string, class-string>
     */
    private const RENDERER_MAP = [
        PageInterface::class => PageRenderer::class,
        ViewInterface::class => ViewRenderer::class,
        PartialViewInterface::class => PartialRenderer::class,
    ];

    /**
     * Holds the initialized renderer instances.
     *
     * @var array<class-string, ComponentRendererInterface>
     */
    private array $rendererMap = [];

    /**
     * Creates and configures the ComponentRenderingService with all its dependencies.
     *
     * This is the main entry point of the factory. It orchestrates the initialization
     * of renderers and the creation of the final service.
     *
     * @param ContextBuilderInterface $contextBuilder The context builder service.
     * @param TemplateEngineInterface $engine The template execution engine.
     * @param TemplateProcessingServiceInterface $templateProcessor The service for resolving template paths.
     * @return ComponentRenderingService A fully configured and ready-to-use instance.
     */
    public function create(
        ContextBuilderInterface $contextBuilder,
        TemplateEngineInterface $engine,
        TemplateProcessingServiceInterface $templateProcessor
    ): ComponentRenderingService {
        $this->initializeRenderers($contextBuilder, $engine, $templateProcessor);
        return new ComponentRenderingService($this->rendererMap, $engine, $templateProcessor);
    }

    /**
     * Initializes the specialist renderer instances based on the static map.
     *
     * This method iterates through the RENDERER_MAP, instantiates each renderer
     * with the required shared dependencies, and populates the instance-level
     * renderer map.
     *
     * @param ContextBuilderInterface $contextBuilder
     * @param TemplateEngineInterface $engine
     * @param TemplateProcessingServiceInterface $templateProcessor
     */
    private function initializeRenderers(
        ContextBuilderInterface $contextBuilder,
        TemplateEngineInterface $engine,
        TemplateProcessingServiceInterface $templateProcessor
    ): void {
        foreach (self::RENDERER_MAP as $component => $rendererClass) {
            $renderer = new $rendererClass($contextBuilder, $engine, $templateProcessor);
            $this->validateRendererInstance($renderer, $rendererClass);
            $this->rendererMap[$component] = $renderer;
        }
    }

    /**
     * Validates that the renderer instance implements the required interface.
     *
     * This method checks if the created renderer instance is of the expected type
     * and throws an exception if it does not implement ComponentRendererInterface.
     *
     * @param object $renderer The renderer instance to validate.
     * @param string $rendererClass The class name of the renderer for error reporting.
     * @throws RuntimeException If the renderer does not implement the required interface.
     */
    private function validateRendererInstance(
        object $renderer,
        string $rendererClass
    ): void {
        if (!$renderer instanceof ComponentRendererInterface) {
            throw new RuntimeException(sprintf(
                'Renderer class %s must implement ComponentRendererInterface',
                $rendererClass
            ));
        }
    }
}
