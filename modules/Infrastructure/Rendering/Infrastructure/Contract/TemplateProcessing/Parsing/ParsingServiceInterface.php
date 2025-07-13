<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\TemplateProcessing\Parsing;

/**
 * Defines the contract for the structural template parsing service.
 *
 * This interface acts as the main orchestrator for the parsing process,
 * resolving template inheritance (@extends) and extracting all sections
 * (@section) to produce a final ParsedTemplate object.
 */
interface ParsingServiceInterface
{
    /**
     * Parses the content of a template to resolve its inheritance structure.
     *
     * @param string $initialContent The raw content of the initial (child) template.
     * @return ParsedTemplateInterface A value object containing the final layout content
     * and an array of all extracted sections.
     */
    public function parse(string $initialContent): ParsedTemplateInterface;
}
