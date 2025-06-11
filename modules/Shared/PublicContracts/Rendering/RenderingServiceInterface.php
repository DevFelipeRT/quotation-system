<?php

declare(strict_types=1);

namespace PublicContracts\Rendering;

use Rendering\Domain\Contract\ViewInterface;

/**
 * RenderingServiceInterface
 *
 * Contract for application services responsible for orchestrating the rendering
 * of complete views and partial fragments. Provides a unified interface for
 * delivering rendered output to the presentation layer or controllers.
 *
 * Implementations should encapsulate all coordination logic necessary to render
 * full views or partials, delegating the actual rendering to infrastructure components.
 *
 * @author
 */
interface RenderingServiceInterface
{
    /**
     * Renders a complete view and returns the resulting output string.
     *
     * @param ViewInterface $view Immutable view object encapsulating template and data.
     * @return string Rendered output (e.g., HTML, JSON).
     */
    public function render(ViewInterface $view): string;

    /**
     * Renders a partial template fragment.
     *
     * @param string $partialFileName Relative filename of the partial.
     * @param array<string, mixed> $data Optional associative data for the partial.
     * @return string Rendered partial content.
     */
    public function renderPartial(string $partialFileName, array $data = []): string;
}
