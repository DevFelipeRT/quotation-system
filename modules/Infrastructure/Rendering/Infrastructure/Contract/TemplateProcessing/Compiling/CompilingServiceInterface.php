<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\TemplateProcessing\Compiling;

/**
 * Defines the contract for the high-level template compiling service.
 *
 * This interface acts as the primary orchestrator for the entire compilation
 * process. A class implementing this contract manages the template inheritance
 * state (@extends, @section) and executes a chain of individual compiler passes
 * in the correct order to produce the final, executable PHP code.
 */
interface CompilingServiceInterface
{
    /**
     * Compiles the raw content of a template into an executable PHP string.
     *
     * @param string $content The template content to be compiled.
     * @return string The compiled PHP code, ready to be cached and executed.
     */
    public function compile(string $content): string;
}
