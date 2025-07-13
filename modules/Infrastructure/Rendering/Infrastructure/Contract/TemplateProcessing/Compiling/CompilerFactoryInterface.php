<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\TemplateProcessing\Compiling;

/**
 * Defines the contract for a unified factory that creates all template compilers.
 *
 * This factory is the single source of truth for instantiating both stateless
 * compiler passes and stateful compilers that require specific data, like the
 * YieldCompiler.
 */
interface CompilerFactoryInterface
{
    /**
     * Returns an ordered, iterable collection of all stateless compiler passes.
     *
     * The order of the compilers in the returned iterable is critical for the
     * correct transformation of the template content.
     *
     * @return iterable<CompilerInterface>
     */
    public function getStatelessCompilers(): iterable;

    /**
     * Creates a new stateful YieldCompiler with the necessary section data.
     *
     * @param array<string, string> $sections The captured section data.
     * @return CompilerInterface The configured YieldCompiler instance.
     */
    public function createYieldCompiler(array $sections): CompilerInterface;
}
