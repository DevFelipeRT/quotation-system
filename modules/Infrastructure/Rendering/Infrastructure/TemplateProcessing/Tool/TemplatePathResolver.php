<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Tool;

use Rendering\Domain\Trait\Validation\TemplateFileValidationTrait;
use Rendering\Domain\ValueObject\Shared\Directory;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplatePathResolverInterface;
use RuntimeException;

/**
 * Resolves a template name into an absolute, validated file path using a
 * guaranteed-valid base directory.
 */
final class TemplatePathResolver implements TemplatePathResolverInterface
{
    use TemplateFileValidationTrait;

    private readonly string $baseViewsPath;

    /**
     * @param Directory $viewsDirectory A value object representing the validated base views directory.
     */
    public function __construct(private readonly Directory $viewsDirectory)
    {
        $this->baseViewsPath = $this->viewsDirectory->path();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $templateName): string
    {
        // Delegate initial input validation to the trait.
        self::validateTemplateFile($templateName);

        // Add .phtml extension if not present
        if (!str_ends_with($templateName, '.phtml')) {
            $templateName .= '.phtml';
        }

        $normalizedTemplateName = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $templateName);
        $potentialPath = $this->baseViewsPath . DIRECTORY_SEPARATOR . ltrim($normalizedTemplateName, DIRECTORY_SEPARATOR);

        $realPath = realpath($potentialPath);

        if ($realPath === false) {
            throw new RuntimeException("Template source file not found or is not accessible: {$potentialPath}");
        }

        if (!str_starts_with($realPath, $this->baseViewsPath)) {
            throw new RuntimeException("Resolved template path is outside of the allowed views directory.");
        }

        return $realPath;
    }
}
