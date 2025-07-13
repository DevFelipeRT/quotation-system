<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing;

use Rendering\Domain\ValueObject\Shared\Directory;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateProcessingServiceInterface;
use Rendering\Infrastructure\TemplateProcessing\Compiling\CompilingServiceFactory;
use Rendering\Infrastructure\TemplateProcessing\Parsing\ParsingServiceFactory;
use Rendering\Infrastructure\TemplateProcessing\Tool\TemplateCache;
use Rendering\Infrastructure\TemplateProcessing\Tool\TemplatePathResolver;

/**
 * Factory responsible for creating and wiring the complete template processing service
 * with all its dependencies.
 * 
 * This factory encapsulates the instantiation logic for the entire template processing
 * subsystem, including parsing, compiling, caching, and path resolution.
 */
final class ProcessingServiceFactory
{
    /**
     * Creates a fully configured TemplateProcessingService with all its dependencies.
     *
     * @param Directory $viewsDirectory The validated directory containing view files.
     * @param Directory $cacheDirectory The validated cache directory for compiled templates.
     * @param bool $debugMode When true, templates are recompiled on every request.
     * @return TemplateProcessingServiceInterface The configured template processing service.
     */
    public static function create(
        Directory $viewsDirectory,
        Directory $cacheDirectory,
        bool $debugMode = true
    ): TemplateProcessingServiceInterface {
        $pathResolver = new TemplatePathResolver($viewsDirectory);
        $parsingService = ParsingServiceFactory::create($viewsDirectory);
        $templateCompiler = CompilingServiceFactory::create($parsingService);
        $templateCache = new TemplateCache($cacheDirectory);
        
        return new TemplateProcessingService(
            $pathResolver,
            $templateCache,
            $templateCompiler,
            $debugMode
        );
    }
}
