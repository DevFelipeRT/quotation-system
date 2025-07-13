<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\Compiler;

use Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\AbstractDirectiveCompiler;

/**
 * A compiler pass that replaces @yield directives with captured section content.
 *
 * This compiler extends the AbstractDirectiveCompiler and provides a custom
 * implementation for the buildParameterizedReplacement method to handle the
 * specific logic of the @yield directive.
 */
final class YieldCompiler extends AbstractDirectiveCompiler
{
    /**
     * {@inheritdoc}
     */
    protected const PARAMETERIZED_DIRECTIVES = [
        'yield' => '/@yield\s*\(/',
    ];

    /**
     * The collection of section content captured from a child template.
     * @var array<string, string>
     */
    private readonly array $sections;

    /**
     * @param array<string, string> $sections The array of captured sections.
     */
    public function __construct(array $sections)
    {
        $this->sections = $sections;
    }

    /**
     * {@inheritdoc}
     *
     * Provides the specific replacement logic for the @yield directive.
     */
    protected function buildParameterizedReplacement(string $name, string $expression): string
    {
        // The expression will be the quoted section name, e.g., "'content'".
        $sectionName = trim($expression, ' \'"');

        return $this->sections[$sectionName] ?? "<!-- Section '{$sectionName}' not found -->";
    }
}
