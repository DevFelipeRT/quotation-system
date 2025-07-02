<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\TemplateProcessing;

use RuntimeException;

/**
 * Defines the contract for a service that resolves template names into absolute file paths.
 *
 * This interface decouples any component that needs to access a template file from
 * the underlying filesystem structure and validation logic. Its single responsibility
 * is to safely transform a relative template identifier into a full, verified,
 * and absolute filesystem path.
 */
interface TemplatePathResolverInterface
{
    /**
     * Resolves a template name into a full, validated, and absolute file path.
     *
     * Implementations must ensure the resulting path is a readable file and
     * should prevent directory traversal attacks.
     *
     * @param string $templateName The relative name of the template (e.g., 'pages/home.phtml').
     * @return string The absolute, readable path to the template file.
     * @throws RuntimeException if the file is not found, is not readable, or if the name is invalid.
     */
    public function resolve(string $templateName): string;
}