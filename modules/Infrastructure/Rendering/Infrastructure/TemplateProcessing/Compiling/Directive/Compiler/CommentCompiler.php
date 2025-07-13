<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\Compiler;

use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\CompilerInterface;

/**
 * A compiler pass that removes all template comments.
 *
 * This compiler is responsible for stripping out all content enclosed
 * within {{-- --}} tags, ensuring they do not appear in the final
 * compiled output.
 */
final class CommentCompiler implements CompilerInterface
{
    /**
     * {@inheritdoc}
     */
    public function compile(string $content): string
    {
        return preg_replace('/\{\{--.*?--\}\}/s', '', $content);
    }
}
