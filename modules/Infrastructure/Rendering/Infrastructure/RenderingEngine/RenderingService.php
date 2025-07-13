<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine;

use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Domain\Contract\Service\RenderingServiceInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\ComponentRenderingServiceInterface;

/**
 * High-level rendering service that acts as the main entry point for the rendering engine.
 *
 * This service delegates the actual rendering work to the ComponentRenderingService,
 * providing a clean and simple interface for the application layer.
 */
final class RenderingService implements RenderingServiceInterface
{
    /**
     * @param ComponentRenderingServiceInterface $componentRenderingService The service responsible for component rendering.
     */
    public function __construct(
        private readonly ComponentRenderingServiceInterface $componentRenderingService
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function render(RenderableInterface $renderable): string
    {
        return $this->componentRenderingService->render($renderable);
    }
}
