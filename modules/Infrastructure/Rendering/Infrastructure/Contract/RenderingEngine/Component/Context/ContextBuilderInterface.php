<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\RenderingEngine\Component\Context;

use Rendering\Domain\Contract\RenderableInterface;

/**
 * Defines the contract for a specialized context builder.
 *
 * Each implementation is responsible for building the RenderContext for a specific
 * type of RenderableInterface. The orchestrator uses a dispatch map to select the
 * correct builder based on the renderable's class name.
 */
interface ContextBuilderInterface
{
    /**
     * Builds the rendering context for the given renderable object.
     *
     * @param RenderableInterface $renderable The object to build the context for.
     * @return RenderContextInterface The fully prepared rendering context.
     */
    public function build(RenderableInterface $renderable): RenderContextInterface;
}
