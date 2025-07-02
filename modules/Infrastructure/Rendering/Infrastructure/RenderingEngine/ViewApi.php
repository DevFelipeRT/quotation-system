<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine;


use Rendering\Domain\Contract\PartialProviderInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\TemplateRendererInterface;

/**
 * Provides a public API for templates to interact with the rendering system.
 *
 * This class is injected into every template's scope and acts as a safe
 * bridge to the underlying rendering engine.
 */
final class ViewApi
{
    /**
     * @param TemplateRendererInterface $renderer The main rendering engine.
     * @param PartialProviderInterface|null $context The current rendering context (a Page or a PartialView).
     */
    public function __construct(
        private readonly TemplateRendererInterface $renderer,
        private readonly ?PartialProviderInterface $context
    ) {
    }

    /**
     * Implements the runtime logic for the @include() directive.
     */
    public function include(string $templateFile, array $data = []): string
    {
        return $this->renderer->renderIncludedPartial($templateFile, $data);
    }

    /**
     * Implements the runtime logic for the @partial() directive.
     */
    public function renderPartial(string $identifier): string
    {
        if ($this->context === null) {
            return '';
        }

        $partials = $this->context->partials();

        if (!isset($partials[$identifier])) {
            return '';
        }

        // This will recursively call renderPartial on the sub-component
        return $this->renderer->renderPartial($partials[$identifier]);
    }
}
