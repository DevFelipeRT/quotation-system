<?php 

declare(strict_types=1);

namespace Rendering\Domain\Trait\Validation;

use InvalidArgumentException;

trait TemplateFileValidationTrait
{
    /**
     * Validates that the provided template file path is a non-empty string.
     *
     * @param string $templateFile The template file path to validate
     * @throws InvalidArgumentException When the template file path is empty or not a string
     */
    public static function validateTemplateFile(string $templateFile): void
    {
        if (empty($templateFile) || !is_string($templateFile)) {
            throw new InvalidArgumentException("Template file name must be a non-empty string.");
        }
        if (str_contains($templateFile, '..')) {
            throw new InvalidArgumentException("Invalid template name: directory traversal ('..') is not allowed.");
        }
    }
}