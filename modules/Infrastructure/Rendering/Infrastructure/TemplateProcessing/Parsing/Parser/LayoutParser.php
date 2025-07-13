<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Parsing\Parser;

use RuntimeException;

/**
 * A specialist parser responsible for finding and extracting the @extends directive.
 *
 * This parser identifies the parent layout a template inherits from and removes
 * the directive from the content, preparing it for the next parsing stage.
 */
final class LayoutParser extends AbstractParser
{
    /**
     * The regex pattern to match the @extends directive.
     *
     * This pattern captures the layout name specified in the @extends directive.
     * It expects the directive to be in the format: @extends("layout-name").
     */
    protected const DIRECTIVE_PATTERN = '/@extends\s*\(\s*["\']([^"\']+)["\']\s*\)/';

    /**
     * Error message for multiple @extends directives.
     */
    private const MULTIPLE_EXTENDS_ERROR = 'Multiple @extends directives found. Only one is allowed per template.';

    /**
     * {@inheritdoc}
     */
    protected function processMatches(array $matches, string $content): array
    {
        $this->validateSingleDirective($matches);
        
        $layout = $this->extractLayoutName($matches);
        $contentWithoutDirective = $this->removeContentParts($matches[0], $content);
        
        return $this->buildResult($layout, $contentWithoutDirective);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildEmptyResult(string $content): array
    {
        return $this->buildResult(null, $content);
    }

    /**
     * Validates that only one @extends directive is present.
     *
     * @param array $matches The regex matches containing all found directives.
     * 
     * @throws RuntimeException If multiple @extends directives are found.
     */
    private function validateSingleDirective(array $matches): void
    {
        if (count($matches[0]) > 1) {
            throw new RuntimeException(self::MULTIPLE_EXTENDS_ERROR);
        }
    }

    /**
     * Extracts the layout name from the regex matches.
     *
     * @param array $matches The regex matches containing the directive and layout name.
     * 
     * @return string The layout name extracted from the first match.
     */
    private function extractLayoutName(array $matches): string
    {
        return $matches[1][0];
    }

    /**
     * Builds the standardized result array.
     *
     * @param string|null $layout The name of the layout, or null if not found.
     * @param string $content The remaining content after processing.
     * 
     * @return array An associative array with 'layout' and 'content' keys.
     */
    private function buildResult(?string $layout, string $content): array
    {
        return [
            'layout' => $layout,
            'content' => $content,
        ];
    }
}