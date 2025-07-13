<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract\Service;

/**
 * Defines the contract for the main Rendering Facade.
 *
 * This interface provides a single, simplified entry point for the entire
 * rendering module. It offers a fluent API to build and render a complete
 * page, hiding the underlying complexity of builders, factories, and renderers.
 */
interface RenderingFacadeInterface extends
    BuildingServiceInterface,
    RenderingServiceInterface
{
}