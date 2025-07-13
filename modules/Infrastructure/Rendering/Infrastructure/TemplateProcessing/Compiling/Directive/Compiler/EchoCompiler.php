<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\Compiler;

use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\CompilerInterface;

/**
 * A compiler pass that transforms template echo statements into PHP.
 *
 * This compiler handles two types of echo syntax:
 * - `{{ $variable }}`: Compiles to a safely escaped PHP echo statement.
 * - `{{{ $variable }}}`: Compiles to a raw, unescaped PHP echo statement.
 */
final class EchoCompiler implements CompilerInterface
{
    /**
     * {@inheritdoc}
     */
    public function compile(string $content): string
    {
        $contentWithRawEchoes = $this->compileRawEcho($content);
        $contentWithEscapedEchoes = $this->compileEscapedEcho($contentWithRawEchoes);
        return $contentWithEscapedEchoes;
    }

    /**
     * Compiles raw echo statements without escaping.
     *
     * This method is used to compile the `{{{ $variable }}}` syntax.
     *
     * @param string $content The template content containing raw echo statements.
     * @return string The compiled PHP code with raw echo statements.
     */
    private function compileRawEcho(string $content): string
    {
        return preg_replace('/\{\{\{\s*(.+?)\s*\}\}\}/s', '<?= $1 ?>', $content);
    }

    /**
     * Compiles escaped echo statements with HTML escaping.
     *
     * This method is used to compile the `{{ $variable }}` syntax.
     *
     * @param string $content The template content containing escaped echo statements.
     * @return string The compiled PHP code with escaped echo statements.
     */
    private function compileEscapedEcho(string $content): string
    {
        return preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?= htmlspecialchars((string) ($1 ?? \'\'), ENT_QUOTES, \'UTF-8\') ?>', $content);
    }
}
