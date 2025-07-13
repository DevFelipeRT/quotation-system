<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\RenderingEngine;

use RuntimeException;

/**
 * Defines the contract for a template execution engine.
 *
 * This interface provides a common abstraction for different template rendering
 * engines (e.g., native PHP, Twig, Blade). It ensures that the core rendering
 * service can remain decoupled from the specific implementation details of how
 * a template is executed.
 */
interface TemplateEngineInterface
{
    /**
     * Executes a template file with the given data and returns its output.
     *
     * @param string $templatePath The absolute path to the compiled template file.
     * @param array<string, mixed> $data The associative array of variables to be
     * extracted into the template's local scope.
     * @return string The rendered HTML content.
     * @throws RuntimeException If an error occurs during template execution.
     */
    public function execute(string $templatePath, array $data): string;
}
