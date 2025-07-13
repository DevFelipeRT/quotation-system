<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Parsing;

use Rendering\Domain\ValueObject\Shared\Directory;
use Rendering\Infrastructure\Contract\TemplateProcessing\Parsing\ParsingServiceInterface;
use Rendering\Infrastructure\TemplateProcessing\Parsing\Parser\LayoutParser;
use Rendering\Infrastructure\TemplateProcessing\Parsing\Parser\SectionParser;
use Rendering\Infrastructure\TemplateProcessing\Tool\TemplatePathResolver;

/**
 * Factory responsible for creating and wiring the template parsing service
 * with all its dependencies.
 * 
 * This factory encapsulates the instantiation logic for the parsing subsystem,
 * reducing complexity in the main kernel.
 */
final class ParsingServiceFactory
{
    /**
     * Creates a fully configured TemplateParsingService with all its dependencies.
     *
     * @param Directory $viewsDirectory The validated directory containing view files.
     * @return ParsingServiceInterface The configured parsing service.
     */
    public static function create(Directory $viewsDirectory): ParsingServiceInterface
    {
        $layoutParser = new LayoutParser();
        $sectionParser = new SectionParser();
        $pathResolver = new TemplatePathResolver($viewsDirectory);
        
        return new TemplateParsingService(
            $layoutParser,
            $sectionParser,
            $pathResolver
        );
    }
}
