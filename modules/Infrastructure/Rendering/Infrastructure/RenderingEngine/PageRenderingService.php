<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine;

use Rendering\Application\Contract\PageRenderingServiceInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\TemplateRendererInterface;
use Rendering\Domain\Contract\PageInterface;

/**
 * Orchestrates the rendering of a complete page by assembling its components.
 *
 * This service takes a composite Page object and uses a TemplateRenderer to
 * render each part (header, view, footer, etc.) in the correct order,
 * finally joining them into a single HTML document.
 */
final class PageRenderingService implements PageRenderingServiceInterface
{
    /**
     * @param TemplateRendererInterface $templateRenderer The low-level engine for rendering individual templates.
     */
    public function __construct(
        private readonly TemplateRendererInterface $templateRenderer
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function renderPage(PageInterface $page): string
    {
        $htmlParts = [];

        // Render the Header
        $htmlParts[] = $this->templateRenderer->renderPartial($page->header());

        // Render the main View, passing the entire Page as context
        $htmlParts[] = $this->templateRenderer->renderView($page->view(), $page);

        // Render the Footer
        $htmlParts[] = $this->templateRenderer->renderPartial($page->footer());

        // Join all the rendered parts into the final document
        return implode("\n", $htmlParts);
    }
}