<?php

declare(strict_types=1);

namespace Logging\Security\Validation\Services;

use Logging\Security\Validation\Tools\StringValidationTrait;
use PublicContracts\Logging\Config\ValidationConfigInterface;
use Logging\Domain\Exception\InvalidLogDirectoryException;

/**
 * DirectoryValidator
 *
 * Validates and normalizes directory paths used for log storage.
 * Enforces non-emptiness, prevents use of root and parent traversal,
 * and checks for forbidden characters as defined by the domain configuration.
 * Throws a domain-specific exception on validation failure.
 */
final class DirectoryValidator
{
    use StringValidationTrait;

    private string $directoryRoot;
    private string $directoryTraversal;
    private string $forbiddenCharsRegex;

    /**
     * @param ValidationConfigInterface $config Configuration provider for directory validation.
     */
    public function __construct(ValidationConfigInterface $config)
    {
        $this->directoryRoot        = $config->directoryRootString();
        $this->directoryTraversal   = $config->directoryTraversalString();
        $this->forbiddenCharsRegex  = $config->stringForbiddenCharsRegex();
    }

    /**
     * Validates a directory path for log storage.
     *
     * Trims, removes null bytes, prevents use of root directory, parent directory traversal,
     * and forbidden characters. Returns the cleaned path on success.
     *
     * @param string $path Directory path to validate.
     * @return string      Validated and normalized directory path.
     *
     * @throws InvalidLogDirectoryException If the path is empty, root, contains traversal, or forbidden characters.
     */
    public function validate(string $path): string
    {
        // Normalize path: trim, remove null bytes, remove trailing slashes
        $clean = rtrim(str_replace("\0", '', $this->cleanString($path)), "/\\");

        if ($this->isEmpty($clean) || $clean === $this->directoryRoot) {
            throw InvalidLogDirectoryException::empty();
        }
        if (strpos($clean, $this->directoryTraversal) !== false) {
            throw InvalidLogDirectoryException::unsafe('contains parent directory traversal');
        }
        if ($this->hasForbiddenChars($clean, $this->forbiddenCharsRegex)) {
            throw InvalidLogDirectoryException::unsafe('contains forbidden characters');
        }

        return $clean;
    }
}
