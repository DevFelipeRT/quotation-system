<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling\Directive;

use LogicException;
use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\CompilerFactoryInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\CompilerInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\DirectiveCompilingServiceInterface;

/**
 * Service responsible for applying directive compilers in the correct order.
 */
final class DirectiveCompilingService implements DirectiveCompilingServiceInterface
{
    /**
     * Defines the fixed, explicit order in which compiler passes must be executed.
     */
    private const COMPILATION_ORDER = [
        Compiler\CommentCompiler::class,
        Compiler\EchoCompiler::class,
        Compiler\ConditionalCompiler::class,
        Compiler\LoopCompiler::class,
        Compiler\PartialCompiler::class,
        Compiler\IncludeCompiler::class,
        Compiler\YieldCompiler::class,
    ];

    /**
     * A local cache for the stateless compilers provided by the factory.
     * @var array<class-string<CompilerInterface>, CompilerInterface>|null
     */
    private ?array $statelessCompilersCache = null;

    /**
     * @param CompilerFactoryInterface $compilerFactory The factory that provides all compiler instances
     */
    public function __construct(
        private readonly CompilerFactoryInterface $compilerFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function compileDirectives(string $content, array $sections = []): string
    {
        foreach (self::COMPILATION_ORDER as $compilerClass) {
            if ($compilerClass === Compiler\YieldCompiler::class) {
                $compiler = $this->compilerFactory->createYieldCompiler($sections);
            } else {
                $compiler = $this->getStatelessCompiler($compilerClass);
            }
            $content = $compiler->compile($content);
        }
        
        return $content;
    }

    /**
     * Retrieves a specific stateless compiler from the collection provided by the factory.
     *
     * @param class-string<CompilerInterface> $compilerClass
     * @return CompilerInterface The requested compiler instance
     * @throws LogicException When the requested compiler is not available
     */
    private function getStatelessCompiler(string $compilerClass): CompilerInterface
    {
        if ($this->statelessCompilersCache === null) {
            $this->statelessCompilersCache = iterator_to_array($this->compilerFactory->getStatelessCompilers());
        }

        if (!isset($this->statelessCompilersCache[$compilerClass])) {
            throw new LogicException("Required compiler '{$compilerClass}' was not provided by the CompilerFactory.");
        }
        return $this->statelessCompilersCache[$compilerClass];
    }
}