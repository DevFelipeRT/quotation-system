<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\Compiler;

use Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\AbstractDirectiveCompiler;

/**
 * A compiler pass that transforms conditional directives into PHP.
 *
 * This compiler handles @if, @elseif, @else, and @endif statements
 * by extending the AbstractDirectiveCompiler.
 */
final class ConditionalCompiler extends AbstractDirectiveCompiler
{
    /**
     * {@inheritdoc}
     */
    protected const PARAMETERIZED_DIRECTIVES = [
        'if'     => '/@if\s*\(/',
        'elseif' => '/@elseif\s*\(/',
    ];

    /**
     * {@inheritdoc}
     */
    protected const PARAMETERLESS_DIRECTIVES = [
        '/@else\b/'  => '<?php else: ?>',
        '/@endif\b/' => '<?php endif; ?>',
    ];
}
