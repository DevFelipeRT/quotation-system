<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\RenderingEngine\Component;

use Rendering\Domain\Contract\RenderableInterface;

/**
 * Defines the contract for a specialized component renderer.
 *
 * Each implementation is responsible for rendering a specific type of RenderableInterface.
 * The orchestrator uses a dispatch map to select the correct renderer based on the
 * renderable's class name, eliminating the need for a `supports` method.
 */
interface ComponentRendererInterface
{
    /**
     * Renders the given renderable object into an HTML string.
     *
     * This method contains the core logic for rendering a specific component,
     * orchestrating the context building and template execution.
     *
     * @param RenderableInterface $renderable The object to be rendered.
     * @param ComponentRenderingServiceInterface $renderer The main rendering orchestrator,
     * passed to allow the ViewApi to delegate recursive rendering calls (e.g., for included partials).
     * @return string The rendered HTML output.
     */
    public function render(RenderableInterface $renderable, ComponentRenderingServiceInterface $renderer): string;
}
