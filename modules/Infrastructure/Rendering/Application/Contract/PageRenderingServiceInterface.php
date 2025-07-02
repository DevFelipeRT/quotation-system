<?php

declare(strict_types=1);

namespace Rendering\Application\Contract;

use Rendering\Domain\Contract\PageInterface;

/**
 * Defines the contract for the high-level page rendering service.
 *
 * This service acts as the primary entry point for rendering a complete page.
 * It is responsible for orchestrating the rendering of all page components
 * (header, view, footer, etc.) in the correct order.
 */
interface PageRenderingServiceInterface
{
    /**
     * Renders a complete HTML page from a composite Page object.
     *
     * @param PageInterface $page The page object containing all components to be rendered.
     * @return string The fully rendered HTML document.
     */
    public function renderPage(PageInterface $page): string;
}