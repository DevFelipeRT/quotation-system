<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing;

use Rendering\Domain\Shared\ValueObject\Directory;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplatePathResolverInterface;
use RuntimeException;

/**
 * Resolves a template name into an absolute, validated file path using a
 * guaranteed-valid base directory.
 */
final class TemplatePathResolver implements TemplatePathResolverInterface
{
    /**
     * @param Directory $viewsDirectory A value object representing the validated base views directory.
     */
    public function __construct(private readonly Directory $viewsDirectory)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $templateName): string
    {
        // Security check for directory traversal attempts.
        if (str_contains($templateName, '..')) {
            throw new RuntimeException("Invalid template name: directory traversal is not allowed.");
        }

        // Normalize the slashes in the template name to match the OS.
        $normalizedTemplateName = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $templateName);

        // Build the full path using the correct directory separator.
        $fullPath = $this->viewsDirectory->path() . DIRECTORY_SEPARATOR . ltrim($normalizedTemplateName, DIRECTORY_SEPARATOR);

        // The base directory is already validated by the Directory VO.
        // We only need to validate the final file path.
        if (!is_file($fullPath) || !is_readable($fullPath)) {
            throw new RuntimeException("Template source file not found or not readable: {$fullPath}");
        }

        return $fullPath;
    }
}
