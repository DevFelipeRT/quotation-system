<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\TemplateProcessing;

use RuntimeException;

/**
 * Defines the contract for the high-level template processing service.
 *
 * This interface acts as a Facade for the entire template compilation subsystem.
 * A class implementing this contract is responsible for taking a template identifier
 * and returning the path to a fully compiled, executable PHP script, handling
 * caching and recompilation transparently.
 */
interface TemplateProcessingServiceInterface
{
    /**
     * Resolves a template name into a path to a ready-to-execute compiled PHP file.
     *
     * This method orchestrates the caching and compilation logic. It checks if a
     * valid, up-to-date cached version of the template exists. If not, it
     * triggers a recompilation and updates the cache.
     *
     * @param string $templateName The identifier for the template (e.g., 'pages/home.phtml').
     * @return string The absolute path to the compiled PHP file.
     * @throws RuntimeException If the source template file cannot be found.
     */
    public function resolve(string $templateName): string;
}