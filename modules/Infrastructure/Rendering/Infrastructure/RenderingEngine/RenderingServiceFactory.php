<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine;

use Rendering\Domain\Contract\Service\RenderingServiceInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateProcessingServiceInterface;
use Rendering\Infrastructure\RenderingEngine\Component\ComponentRenderingServiceFactory;
use Rendering\Infrastructure\RenderingEngine\Component\Context\ContextBuilderFactory;
use Rendering\Infrastructure\RenderingEngine\Engine\PhpTemplateEngine;

/**
 * Factory responsible for creating and wiring the complete rendering engine service
 * with all its dependencies.
 * 
 * This factory encapsulates the instantiation logic for the entire rendering engine
 * subsystem, following the same pattern as other service factories.
 */
final class RenderingServiceFactory
{
    /**
     * Creates a fully configured RenderingService with all its dependencies.
     *
     * @param TemplateProcessingServiceInterface $templateProcessingService The template processing service dependency.
     * @return RenderingServiceInterface The configured rendering service.
     */
    public static function create(
        TemplateProcessingServiceInterface $templateProcessingService
    ): RenderingServiceInterface {
        $contextBuilderFactory = new ContextBuilderFactory();
        $contextBuilder = $contextBuilderFactory->create();
        $engine = new PhpTemplateEngine();
        
        $componentRenderingServiceFactory = new ComponentRenderingServiceFactory();
        $componentRenderingService = $componentRenderingServiceFactory->create(
            $contextBuilder,
            $engine,
            $templateProcessingService
        );
        
        return new RenderingService($componentRenderingService);
    }
}
