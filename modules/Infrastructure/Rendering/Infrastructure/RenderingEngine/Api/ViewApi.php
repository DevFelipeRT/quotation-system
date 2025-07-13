<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Api;

use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\ComponentRenderingServiceInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\ViewApiInterface;

/**
 * Provides a public API for templates to interact with the rendering system.
 *
 * This class is injected into every template's scope as `$view` and acts as a
 * safe bridge to the underlying rendering engine.
 */
final class ViewApi implements ViewApiInterface
{
    public function __construct(
        private readonly ComponentRenderingServiceInterface $renderer,
        private readonly ?RenderableInterface $context
    ) {
    }

    /**
     * {@inheritdoc}
     * 
     * Delegates template file rendering to the main renderer, enabling templates to include
     * other templates without requiring knowledge of the underlying rendering engine details.
     * 
     * @param string $templateFile The template file identifier to render.
     * @param array<string, mixed> $data Optional data to pass to the template.
     */
    public function include(string $templateFile, array $data = []): string
    {
        return $this->renderer->renderTemplateFile($templateFile, $data, $this->context);
    }

    /**
     * {@inheritdoc}
     * 
     * Renders a partial view by its identifier, allowing templates to include reusable components.
     * This method abstracts the complexity of finding and rendering partials, providing a simple
     * interface for templates to include partials by their unique identifier.
     * 
     * @param string $identifier The unique identifier for the partial to render.
     * @return string The rendered HTML output of the partial.
     * 
     * If the partial is not found, it returns an empty string.
     */
    public function renderPartial(string $identifier): string
    {
        $partial = $this->findPartialByIdentifier($identifier);

        if ($partial === null) {
            return '';
        }

        return $this->renderer->render($partial);
    }

    /**
     * Finds a partial by its identifier from the current context.
     *
     * This method checks if the context has a collection of partials and retrieves
     * the one matching the provided identifier. If no matching partial is found,
     * it returns null.
     *
     * @param string $identifier The unique identifier for the partial.
     * @return RenderableInterface|null The found partial or null if not found.
     */
    private function findPartialByIdentifier(string $identifier): ?RenderableInterface
    {
        if ($this->context === null || $this->context->partials() === null) {
            return null;
        }

        $partialsCollection = $this->context->partials();

        if (!$partialsCollection->has($identifier)) {
            return null;
        }

        return $partialsCollection->get($identifier);
    }
}