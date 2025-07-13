<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Parsing\Parser;

/**
 * A specialist parser responsible for finding and extracting all @section blocks.
 *
 * This parser iterates through the template content, captures the name and
 * raw content of each section, and removes the section definitions from the
 * string, preparing it for the next parsing stage.
 */
final class SectionParser extends AbstractParser
{
    /**
     * The regex pattern to match @section directives.
     *
     * This pattern captures the section name and its content, allowing for
     * nested sections and multiline content.
     */
    protected const DIRECTIVE_PATTERN = '/@section\s*\(\s*["\']([^"\']+)["\']\s*\)(.*?)@endsection/s';

    /**
     * {@inheritdoc}
     */
    protected function processMatches(array $matches, string $content): array
    {
        $sections = $this->extractSections($matches);
        $contentWithoutSections = $this->removeContentParts($matches[0], $content);

        return $this->buildResult($sections, $contentWithoutSections);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildEmptyResult(string $content): array
    {
        return $this->buildResult([], $content);
    }

    /**
     * Extracts sections as an associative array from the regex matches.
     *
     * @param array $matches The regex matches containing section names and content.
     * 
     * @return array An associative array with section names as keys and content as values.
     */
    private function extractSections(array $matches): array
    {
        $sections = [];
        $sectionNames = $matches[1];
        $sectionContents = $matches[2];

        for ($i = 0; $i < count($sectionNames); $i++) {
            $sectionName = $sectionNames[$i];
            $sectionContent = trim($sectionContents[$i]);
            $sections[$sectionName] = $sectionContent;
        }

        return $sections;
    }

    /**
     * Builds the standardized result array.
     *
     * @param array $sections The extracted sections as an associative array.
     * @param string $content The remaining content after processing.
     * 
     * @return array An associative array with 'sections' and 'content' keys.
     */
    private function buildResult(array $sections, string $content): array
    {
        return [
            'sections' => $sections,
            'content' => $content,
        ];
    }
}
