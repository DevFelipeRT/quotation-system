<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling;

use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\CompilingServiceInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\Parsing\ParsingServiceInterface;
use Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\DirectiveCompilerFactory;
use Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\DirectiveCompilingService;

/**
 * Factory responsible for creating and wiring the template compiling service
 * with all its dependencies.
 * 
 * This factory encapsulates the instantiation logic for the compiling subsystem,
 * reducing complexity in the main kernel.
 */
final class CompilingServiceFactory
{
    /**
     * Creates a fully configured TemplateCompilingService with all its dependencies.
     *
     * @param ParsingServiceInterface $parsingService The parsing service dependency.
     * @return CompilingServiceInterface The configured compiling service.
     */
    public static function create(ParsingServiceInterface $parsingService): CompilingServiceInterface
    {
        $compilerFactory = new DirectiveCompilerFactory();
        $directiveCompiler = new DirectiveCompilingService($compilerFactory);
        $recursiveCompiler = new RecursiveTemplateCompiler();
        
        return new TemplateCompilingService(
            $parsingService,
            $directiveCompiler,
            $recursiveCompiler
        );
    }
}
