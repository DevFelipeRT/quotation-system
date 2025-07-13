<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Parsing;

use Rendering\Infrastructure\Contract\TemplateProcessing\Parsing\ParserInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\Parsing\ParsingServiceInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\Parsing\ParsedTemplateInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplatePathResolverInterface;
use RuntimeException;

/**
 * Orchestrates the structural parsing of a template by resolving its inheritance
 * chain (@extends) and extracting all of its content blocks (@section).
 */
final class TemplateParsingService implements ParsingServiceInterface
{
    private readonly ParserInterface $layoutParser;
    private readonly ParserInterface $sectionParser;
    private readonly TemplatePathResolverInterface $pathResolver;

    /**
     * @param ParserInterface $layoutParser A parser to find the @extends directive.
     * @param ParserInterface $sectionParser A parser to find all @section blocks.
     * @param TemplatePathResolverInterface $pathResolver A service to resolve template names to file paths.
     */
    public function __construct(
        ParserInterface $layoutParser,
        ParserInterface $sectionParser,
        TemplatePathResolverInterface $pathResolver
    ) {
        $this->layoutParser = $layoutParser;
        $this->sectionParser = $sectionParser;
        $this->pathResolver = $pathResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $initialContent): ParsedTemplateInterface
    {
        $result = $this->parseTemplateHierarchyRecursively($initialContent);

        return new ParsedTemplate($result['finalContent'], $result['allSections']);
    }

    /**
     * Recursively parses the template hierarchy, collecting all sections from
     * child templates and resolving the final layout content.
     *
     * @param string $templateContent The current template content
     * @return array{finalContent: string, allSections: array<string, string>}
     */
    private function parseTemplateHierarchyRecursively(string $templateContent): array
    {
        $currentSections = $this->extractSectionsFrom($templateContent);
        $layoutInfo = $this->extractLayoutFrom($currentSections['content']);
        
        if (!$this->hasParentLayout($layoutInfo)) {
            return $this->buildFinalTemplateResult($layoutInfo['content'], $currentSections['sections']);
        }
        
        $parentResult = $this->processParentLayoutRecursively($layoutInfo['layout']);
        
        // Merge sections (child sections take precedence over parent sections)
        $mergedSections = array_merge($parentResult['allSections'], $currentSections['sections']);
        
        return $this->buildFinalTemplateResult($parentResult['finalContent'], $mergedSections);
    }

    /**
     * Uses the SectionParser to extract all sections from the content.
     *
     * @return array{sections: array<string, string>, content: string}
     */
    private function extractSectionsFrom(string $content): array
    {
        return $this->sectionParser->parse($content);
    }

    /**
     * Uses the LayoutParser to find the parent layout path.
     *
     * @return array{layout: string|null, content: string}
     */
    private function extractLayoutFrom(string $content): array
    {
        return $this->layoutParser->parse($content);
    }

    /**
     * Determines if the template has a parent layout to extend.
     *
     * @param array{layout: string|null, content: string} $layoutInfo
     */
    private function hasParentLayout(array $layoutInfo): bool
    {
        return $layoutInfo['layout'] !== null;
    }

    /**
     * Recursively processes the parent layout template.
     *
     * @param string $layoutName The name of the parent layout
     * @return array{finalContent: string, allSections: array<string, string>}
     */
    private function processParentLayoutRecursively(string $layoutName): array
    {
        $layoutContent = $this->loadLayoutTemplate($layoutName);
        return $this->parseTemplateHierarchyRecursively($layoutContent);
    }

    /**
     * Loads the layout template file and returns its content.
     *
     * @param string $layoutName The name of the layout template
     * @return string The raw content of the layout template
     * @throws RuntimeException If the layout file cannot be loaded
     */
    private function loadLayoutTemplate(string $layoutName): string
    {
        try {
            $layoutPath = $this->pathResolver->resolve($layoutName);
            $content = file_get_contents($layoutPath);
            
            if ($content === false) {
                throw new RuntimeException("Failed to read layout file content");
            }
            
            return $content;
        } catch (RuntimeException $e) {
            throw new RuntimeException("Layout file '{$layoutName}' could not be loaded: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Builds the final template result structure.
     *
     * @param string $content The final template content
     * @param array<string, string> $sections All collected sections
     * @return array{finalContent: string, allSections: array<string, string>}
     */
    private function buildFinalTemplateResult(string $content, array $sections): array
    {
        return [
            'finalContent' => $content,
            'allSections' => $sections
        ];
    }
}