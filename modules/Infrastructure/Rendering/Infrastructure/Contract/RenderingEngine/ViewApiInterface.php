<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\RenderingEngine;

/**
 * Defines the public API contract for templates to interact with the rendering system.
 *
 * This interface specifies the helper methods that will be available within a
 * template's scope (typically via a `$view` variable). It provides a safe and
 * consistent way for templates to request the rendering of sub-components like
 * includes and partials.
 */
interface ViewApiInterface
{
    /**
     * Renders a template file directly by its identifier.
     * Corresponds to the @include() directive.
     *
     * @param string $templateFile The template file identifier to include.
     * @param array<string, mixed> $data Data to be passed to the included template.
     * @return string The rendered HTML of the included partial.
     */
    public function include(string $templateFile, array $data = []): string;

    /**
     * Renders a pre-built partial component by its string identifier.
     * Corresponds to the @partial() directive.
     *
     * @param string $identifier The name of the partial to render from the main Page context.
     * @return string The rendered HTML of the partial, or an empty string if not found.
     */
    public function renderPartial(string $identifier): string;
}