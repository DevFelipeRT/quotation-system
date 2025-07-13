<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling\Directive;

use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\CompilerFactoryInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\CompilerInterface;

/**
 * A concrete implementation of the CompilerFactoryInterface.
 *
 * This factory is the single source of truth for instantiating both stateless
 * compiler passes and stateful compilers that require specific data.
 */
final class DirectiveCompilerFactory implements CompilerFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getStatelessCompilers(): iterable
    {
        $compilers = [
            Compiler\CommentCompiler::class     => new Compiler\CommentCompiler(),
            Compiler\EchoCompiler::class        => new Compiler\EchoCompiler(),
            Compiler\ConditionalCompiler::class => new Compiler\ConditionalCompiler(),
            Compiler\LoopCompiler::class        => new Compiler\LoopCompiler(),
            Compiler\PartialCompiler::class     => new Compiler\PartialCompiler(),
            Compiler\IncludeCompiler::class     => new Compiler\IncludeCompiler(),
        ];
        return $compilers;
    }

    /**
     * {@inheritdoc}
     */
    public function createYieldCompiler(array $sections): CompilerInterface
    {
        return new Compiler\YieldCompiler($sections);
    }
}
