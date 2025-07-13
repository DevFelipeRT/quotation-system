<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Parsing;

use Rendering\Infrastructure\Contract\TemplateProcessing\Parsing\ParsedTemplateInterface;

/**
 * An immutable value object that holds the result of a structural template parse.
 */
final class ParsedTemplate implements ParsedTemplateInterface
{
    /**
     * @param string $content The final template content, typically from the parent layout.
     * @param array<string, string> $sections An associative array of all extracted sections.
     */
    public function __construct(
        private readonly string $content,
        private readonly array $sections
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function getSections(): array
    {
        return $this->sections;
    }
}
