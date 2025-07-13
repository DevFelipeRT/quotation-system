<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract\Service;

use Rendering\Domain\Contract\RenderableInterface;

/**
 * Defines the contract for the rendering service.
 *
 * This interface provides methods to render any renderable object,
 * allowing for a flexible and extensible rendering mechanism.
 */
interface RenderingServiceInterface
{
    /**
     * Renders any renderable object by dispatching it to the correct specialist.
     *
     * @param RenderableInterface $renderable The object to be rendered.
     * @return string The rendered HTML output.
     */
    public function render(RenderableInterface $renderable): string;
}