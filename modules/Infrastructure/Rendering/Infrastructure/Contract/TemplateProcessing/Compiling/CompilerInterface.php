<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\TemplateProcessing\Compiling;

/**
 * Defines the contract for an individual compiler pass.
 *
 * Each class implementing this interface is responsible for a single, specific
 * transformation of the template content, such as compiling echo statements or
 * control structures. This allows the main compiling service to be composed
 * of a chain of these specialized compilers.
 */
interface CompilerInterface
{
    /**
     * Executes a compilation transformation on the given template content.
     *
     * @param string $content The template content string to be transformed.
     * @return string The transformed content.
     */
    public function compile(string $content): string;
}
