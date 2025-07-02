<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\RenderingEngine;

use Rendering\Domain\Contract\PageInterface;
use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Contract\ViewInterface;

/**
 * Defines the contract for the low-level template rendering engine.
 *
 * This service is responsible for executing a single compiled template file
 * with a given data context and returning the resulting HTML string.
 */
interface TemplateRendererInterface
{
    /**
     * Renders a primary View, providing the full Page context.
     * This context is necessary for directives like @partial() to function.
     *
     * @param ViewInterface $view The main content view to render.
     * @param PageInterface $pageContext The parent page object, providing full context.
     * @return string The rendered HTML.
     */
    public function renderView(ViewInterface $view, PageInterface $pageContext): string;

    /**
     * Renders an isolated Partial View.
     * This method can gracefully handle a null partial, returning an empty string.
     *
     * @param PartialViewInterface|null $partial The partial view component to render.
     * @return string The rendered HTML.
     */
    public function renderPartial(?PartialViewInterface $partial): string;

    /**
     * Renders a partial template directly from a file path.
     * This method is intended for internal use by the TemplateHelper for the @include directive.
     *
     * @param string $templateFile The template file identifier.
     * @param array<string, mixed> $data The data to pass to the template.
     * @return string The rendered HTML.
     */
    public function renderIncludedPartial(string $templateFile, array $data): string;
}