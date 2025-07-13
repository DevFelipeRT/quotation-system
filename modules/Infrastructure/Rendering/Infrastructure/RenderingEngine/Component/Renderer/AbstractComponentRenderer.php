<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component\Renderer;

use Rendering\Infrastructure\Contract\RenderingEngine\Component\ComponentRendererInterface;
use RuntimeException;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\ContextBuilderInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\TemplateEngineInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateProcessingServiceInterface;
use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\ComponentRenderingServiceInterface;
use Rendering\Infrastructure\RenderingEngine\Api\ViewApi;

/**
 * Provides a base implementation for a component renderer.
 *
 * This abstract class centralizes the common rendering algorithm, dependencies,
 * and logic, adhering to the DRY principle. It depends solely on abstractions.
 */
abstract class AbstractComponentRenderer implements ComponentRendererInterface
{
    public function __construct(
        private readonly ContextBuilderInterface $contextBuilder,
        private readonly TemplateEngineInterface $engine,
        private readonly TemplateProcessingServiceInterface $templateProcessor
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * This method implements the final, shared rendering algorithm. It orchestrates
     * context building, data preparation, and template execution.
     */
    final public function render(RenderableInterface $renderable, ComponentRenderingServiceInterface $renderer): string
    {
        $templateTarget = $this->getTemplateTarget($renderable);
        $templateName = $this->getTemplateName($templateTarget);
        
        // The context is always built from the original renderable object to ensure
        // the correct data and partials are aggregated (e.g., for a Page).
        $templateData = $this->buildTemplateData($renderable, $renderer);
        
        return $this->executeTemplate($templateName, $templateData);
    }

    /**
     * Gets the primary renderable object that defines the template file name.
     *
     * This method can be overridden by subclasses to handle special cases, such as
     * a Page, where the template name is derived from its associated View.
     */
    protected function getTemplateTarget(RenderableInterface $renderable): RenderableInterface
    {
        return $renderable;
    }

    /**
     * Retrieves the template file name from the renderable object.
     */
    private function getTemplateName(RenderableInterface $renderable): string
    {
        $templateName = $renderable->fileName();

        if (empty(trim($templateName))) {
            throw new RuntimeException(
                sprintf('Renderable object of class "%s" must provide a template file name.', get_class($renderable))
            );
        }

        return $templateName;
    }

    /**
     * Prepares the complete data array to be injected into the template.
     */
    private function buildTemplateData(RenderableInterface $renderable, ComponentRenderingServiceInterface $renderer): array
    {
        $renderContext = $this->contextBuilder->build($renderable);
        $viewApi = new ViewApi($renderer, $renderContext->getApiContext());
        
        $templateData = $renderContext->getData();
        $templateData['view'] = $viewApi;
        
        return $templateData;
    }
    
    /**
     * Executes the template with the prepared data.
     */
    private function executeTemplate(string $templateName, array $templateData): string
    {
        $compiledPath = $this->templateProcessor->resolve($templateName);
        return $this->engine->execute($compiledPath, $templateData);
    }


}
