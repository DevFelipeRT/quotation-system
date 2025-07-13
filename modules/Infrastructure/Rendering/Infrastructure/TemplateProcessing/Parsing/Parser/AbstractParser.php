<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Parsing\Parser;

use Rendering\Infrastructure\Contract\TemplateProcessing\Parsing\ParserInterface;

/**
 * Abstract base class for template directive parsers.
 *
 * This class provides common functionality for parsing template directives,
 * including pattern matching, content validation, and result building.
 * Concrete parsers should extend this class and implement their specific logic.
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     * The regex pattern to match template directives.
     * Must be defined by concrete implementations.
     */
    protected const DIRECTIVE_PATTERN = '';

    /**
     * {@inheritdoc}
     */
    public function parse(string $content): array
    {
        if (empty($content)) {
            return $this->buildEmptyResult($content);
        }

        $matches = $this->findDirectiveMatches($content);
        if (empty($matches)) {
            return $this->buildEmptyResult($content);
        }

        return $this->processMatches($matches, $content);
    }

    /**
     * Finds all directive matches in the content using the pattern.
     *
     * @param string $content The template content to search for directives.
     * 
     * @return array The matches array from preg_match_all, or empty array if no matches.
     */
    protected function findDirectiveMatches(string $content): array
    {
        if (empty(static::DIRECTIVE_PATTERN)) {
            return [];
        }

        $matches = [];
        $matchCount = preg_match_all(static::DIRECTIVE_PATTERN, $content, $matches);
        
        return $matchCount > 0 ? $matches : [];
    }

    /**
     * Removes content using string replacement and returns cleaned content.
     *
     * @param array $contentToRemove Array of strings to remove from content.
     * @param string $content The original template content.
     * 
     * @return string The content with specified strings removed and trimmed.
     */
    protected function removeContentParts(array $contentToRemove, string $content): string
    {
        $cleanedContent = $content;

        foreach ($contentToRemove as $part) {
            $cleanedContent = str_replace($part, '', $cleanedContent);
        }

        return trim($cleanedContent);
    }

    /**
     * Processes the regex matches and returns the parsed result.
     * Must be implemented by concrete classes to define specific parsing logic.
     *
     * @param array $matches The regex matches containing directive information.
     * @param string $content The original template content.
     * 
     * @return array The processed result specific to the parser type.
     */
    abstract protected function processMatches(array $matches, string $content): array;

    /**
     * Builds the result when no matches are found or content is empty.
     * Must be implemented by concrete classes to define their empty result structure.
     *
     * @param string $content The original content.
     * 
     * @return array The empty result specific to the parser type.
     */
    abstract protected function buildEmptyResult(string $content): array;
}
