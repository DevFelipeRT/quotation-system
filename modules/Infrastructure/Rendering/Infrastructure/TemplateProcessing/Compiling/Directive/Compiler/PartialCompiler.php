<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\Compiler;

use Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\AbstractDirectiveCompiler;

/**
 * A compiler pass that transforms @partial directives into PHP render calls.
 *
 * This compiler extends the AbstractDirectiveCompiler and defines the
 * simple replacement rule for the @partial directive.
 */
final class PartialCompiler extends AbstractDirectiveCompiler
{
    /**
     * {@inheritdoc}
     */
    protected const PARAMETERLESS_DIRECTIVES = [
        // The pattern for @partial is treated as a simple replacement,
        // using a backreference ($1) for the captured partial name.
        '/@partial\s*\(\s*["\']([^"\']+)["\']\s*\)/' => '<?= $view->renderPartial("$1") ?>',
    ];
}
