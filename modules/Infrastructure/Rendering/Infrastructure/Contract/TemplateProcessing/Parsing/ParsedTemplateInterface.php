<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\TemplateProcessing\Parsing;

/**
 * Defines the contract for an object that holds the result of a structural parse.
 *
 * This value object encapsulates the final layout content and the collection
 * of all sections extracted from a child template, providing a clean data
 * structure to pass to the next stage of the compilation process.
 */
interface ParsedTemplateInterface
{
    /**
     * Returns the final template content, typically from the parent layout.
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Returns the raw content of all extracted sections.
     *
     * @return array<string, string> An associative array where keys are section names.
     */
    public function getSections(): array;
}
