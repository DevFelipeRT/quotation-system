<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling;

use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\CompilingServiceInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\DirectiveCompilingServiceInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\Parsing\ParsingServiceInterface;

/**
 * Orchestrates template compilation by coordinating parsing, section compilation,
 * and directive processing through specialized services.
 */
final class TemplateCompilingService implements CompilingServiceInterface
{
    /**
     * @param ParsingServiceInterface $parsingService Template structure parser
     * @param DirectiveCompilingServiceInterface $directiveCompiler Directive compiler
     * @param RecursiveTemplateCompiler $recursiveCompiler Recursive content compiler
     */
    public function __construct(
        private readonly ParsingServiceInterface $parsingService,
        private readonly DirectiveCompilingServiceInterface $directiveCompiler,
        private readonly RecursiveTemplateCompiler $recursiveCompiler
    ) {
    }

    /**
     * Compiles template content by orchestrating parsing and compilation steps.
     * 
     * @param string $content Raw template content
     * @return string Compiled template content
     * 
     * {@inheritdoc}
     */
    public function compile(string $content): string
    {
        $content = $this->validateContent($content);
        $parsedTemplate = $this->parseTemplateContent($content);
        $compiledSections = $this->compileSections($parsedTemplate->getSections());
        
        return $this->compileMainContent($parsedTemplate->getContent(), $compiledSections);
    }

    /**
     * Validates template content, returning empty string for invalid content.
     * 
     * @param string $content Template content to validate
     * @return string Validated content or empty string
     */
    private function validateContent(string $content): string
    {
        if (empty(trim($content))) {
            return '';
        }
        return $content;
    }

    /**
     * Parses template content to extract structure and sections.
     * 
     * @param string $content Raw template content
     * @return object Parsed template object with content and sections
     */
    private function parseTemplateContent(string $content): object
    {
        return $this->parsingService->parse($content);
    }

    /**
     * Compiles template sections recursively.
     * 
     * @param array<string, string> $sections Section name to content map
     * @return array<string, string> Compiled sections
     */
    private function compileSections(array $sections): array
    {
        return $this->executeContentCompilation($sections);
    }

    /**
     * Compiles main template content with compiled sections context.
     * 
     * @param string $mainContent Raw main template content
     * @param array<string, string> $compiledSections Compiled sections for reference
     * @return string Final compiled content
     */
    private function compileMainContent(string $mainContent, array $compiledSections): string
    {
        $compiledContent = $this->executeContentCompilation(
            ['main' => $mainContent], 
            $compiledSections
        );
        
        return $compiledContent['main'];
    }

    /**
     * Executes content compilation using the recursive compiler.
     * 
     * @param array<string, string> $contents Content to compile
     * @param array<string, string> $contextSections Sections for yield resolution
     * @return array<string, string> Compiled content
     */
    private function executeContentCompilation(
        array $contents, 
        array $contextSections = []
    ): array {
        return $this->recursiveCompiler->compile(
            $contents,
            $contextSections,
            $this->createDirectiveCompilerCallback()
        );
    }

    /**
     * Creates callback for directive compilation.
     * 
     * @return callable(string, array<string, string>): string
     */
    private function createDirectiveCompilerCallback(): callable
    {
        return fn(string $content, array $sections): string => 
            $this->directiveCompiler->compileDirectives($content, $sections);
    }
}