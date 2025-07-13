<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\RenderingEngine\Component;

use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Domain\Contract\Service\RenderingServiceInterface;

/**
 * Defines the contract for the component rendering service.
 *
 * The service is responsible for receiving any renderable object and
 * dispatching the rendering task to the appropriate specialist renderer.
 */
interface ComponentRenderingServiceInterface extends RenderingServiceInterface
{
    /**
     * Renders a template file directly, without a corresponding domain object.
     *
     * This is used for includes requested from within another template.
     *
     * @param string $templateFile The template file identifier to render.
     * @param array<string, mixed> $data Data to be passed to the included template.
     * @param RenderableInterface|null $parentContext The context of the calling template,
     * to allow nested partials to be resolved.
     * @return string The rendered HTML output.
     */
    public function renderTemplateFile(string $templateFile, array $data, ?RenderableInterface $parentContext): string;
}
