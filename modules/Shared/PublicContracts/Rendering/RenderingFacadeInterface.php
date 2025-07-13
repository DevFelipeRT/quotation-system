<?php

declare(strict_types=1);

namespace PublicContracts\Rendering;

/**
 * The public-facing contract for the Rendering module.
 *
 * Any part of the application that needs to render a page should depend on
 * this interface. It acts as a stable, public alias for the module's
 * internal service contract.
 */
interface RenderingFacadeInterface extends \Rendering\Domain\Contract\Service\RenderingFacadeInterface
{
}
