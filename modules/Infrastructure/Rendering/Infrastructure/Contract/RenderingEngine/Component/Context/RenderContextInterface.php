<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\RenderingEngine\Component\Context;

use Rendering\Domain\Contract\RenderableInterface;

/**
 * Defines the contract for the rendering context.
 *
 * The RenderContext acts as a Data Transfer Object (DTO) that encapsulates all
 * the necessary information for a template to be rendered.
 */
interface RenderContextInterface
{
    /**
     * Gets the final data array to be injected into the template's scope.
     *
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Gets the API context object (e.g., the Page or Partial) that the ViewApi
     * will use to resolve internal calls.
     */
    public function getApiContext(): ?RenderableInterface;
}
