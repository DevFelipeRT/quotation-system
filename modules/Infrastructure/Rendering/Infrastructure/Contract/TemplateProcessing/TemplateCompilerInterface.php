<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\TemplateProcessing;

/**
 * Defines the contract for the core template compiler.
 *
 * This interface abstracts the logic of transforming a raw template string,
 * with custom directives, into executable PHP code. A class implementing this
 * contract is the heart of the template pre-processing system.
 */
interface TemplateCompilerInterface
{
    /**
     * Compiles the string content of a template file into executable PHP code.
     *
     * @param string $content The raw string content of the template file to be compiled.
     * @return string The compiled PHP code as a string, ready to be cached and executed.
     */
    public function compile(string $content): string;
}