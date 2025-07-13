<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\TemplateProcessing\Parsing;

/**
 * Defines the contract for a specialist parser.
 *
 * Each class implementing this interface is responsible for a single, specific
 * structural analysis task, such as finding an @extends directive or
 * extracting the content of all @section directives.
 */
interface ParserInterface
{
    /**
     * Parses the given template content to extract a specific piece of structural information.
     *
     * @param string $content The raw template content to be parsed.
     * @return array An associative array containing the parsed data and the
     * modified content string after removing the parsed directive.
     * For example: ['layout' => 'path', 'content' => '...']
     * or ['sections' => [...], 'content' => '...'].
     */
    public function parse(string $content): array;
}
