<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract;

use Rendering\Domain\Contract\Service\RenderingFacadeInterface;

/**
 * Defines the public-facing contract for the Rendering module.
 *
 * This interface serves as the main entry point for any client code that needs
 * to perform rendering operations.
 */
interface RenderingKernelInterface
{
    /**
     * Retrieves the rendering facade instance.
     *
     * This facade is the primary object that clients will interact with
     * to render views, templates, or other content.
     *
     * @return RenderingFacadeInterface The fully configured rendering facade.
     */
    public function renderer(): RenderingFacadeInterface;
}