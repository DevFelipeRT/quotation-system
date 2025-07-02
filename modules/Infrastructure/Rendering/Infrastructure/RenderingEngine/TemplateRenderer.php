<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine;

use Rendering\Infrastructure\Contract\RenderingEngine\TemplateRendererInterface;
use Rendering\Infrastructure\TemplateProcessing\TemplateProcessingService;
use Rendering\Domain\Contract\PageInterface;
use Rendering\Domain\Contract\PartialProviderInterface;
use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Contract\ViewInterface;

/**
 * The core template rendering engine.
 */
final class TemplateRenderer implements TemplateRendererInterface
{
    public function __construct(
        private readonly TemplateProcessingService $templateProcessor
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function renderView(ViewInterface $view, PageInterface $pageContext): string
    {
        // For a primary view, the context is the entire Page object.
        return $this->execute($view->fileName(), $view->data()->toArray(), $pageContext);
    }

    /**
     * {@inheritdoc}
     */
    public function renderPartial(?PartialViewInterface $partial): string
    {
        // If a null component is passed, render nothing.
        if ($partial === null) {
            return '';
        }
        
        // For a partial view, the context is THE PARTIAL ITSELF,
        // allowing it to render its own nested partials.
        return $this->execute($partial->fileName(), $partial->data(), $partial);
    }
    
    /**
     * {@inheritdoc}
     */
    public function renderIncludedPartial(string $templateFile, array $data): string
    {
        // An @include does not carry over the parent context.
        return $this->execute($templateFile, $data);
    }

    /**
     * The core execution engine for any compiled template.
     */
    private function execute(
        string $templateName,
        array $data,
        ?PartialProviderInterface $context = null
    ): string {
        $compiledPath = $this->templateProcessor->resolve($templateName);

        // The ViewApi now receives the correct context.
        $data['view'] = new ViewApi($this, $context);

        ob_start();
        try {
            (static function (string $__file__, array $__data__): void {
                extract($__data__, EXTR_SKIP);
                include $__file__;
            })($compiledPath, $data);
        } finally {
            return ob_get_clean() ?: '';
        }
    }
}
