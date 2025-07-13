<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Page;

use Rendering\Domain\ValueObject\Shared\Directory;
use Rendering\Infrastructure\Contract\Building\Page\AssetPathResolverInterface;

use RuntimeException;

/**
 * Resolves asset identifiers (both local filenames and external URLs)
 * into fully qualified, web-accessible URLs.
 */
final class AssetPathResolver implements AssetPathResolverInterface
{
    private readonly string $baseWebPath;

    /**
     * @param Directory $resourcesDirectory The validated Directory object for the physical 'resources' folder.
     * @param string $baseWebPath The public base URL for the resources folder (e.g., '/resources').
     */
    public function __construct(
        private readonly Directory $resourcesDirectory,
        string $baseWebPath = '/resources'
    ) {
        $this->baseWebPath = rtrim($baseWebPath, '/');
    }

    /**
     * {@inheritdoc}
     *
     * @param string $assetIdentifier The filename of a local asset (e.g., 'style.css') or a full external URL.
     */
    public function resolve(string $assetIdentifier): string
    {
        // Step 1: Check if the identifier is already a full external URL.
        if (str_starts_with($assetIdentifier, 'http://') || str_starts_with($assetIdentifier, 'https://')) {
            // If it is, return it directly without further processing.
            return $assetIdentifier;
        }

        // --- Logic for local assets ---

        // Step 2: Security check for local filenames.
        if (str_contains($assetIdentifier, '..') || str_contains($assetIdentifier, '/') || str_contains($assetIdentifier, '\\')) {
            throw new RuntimeException("Invalid local asset name '{$assetIdentifier}'. Only filenames are permitted for local assets.");
        }

        // Step 3: Determine the correct subdirectory based on the file extension.
        $subDirectory = $this->getSubDirectoryForAsset($assetIdentifier);
        
        // Step 4: Construct the full relative path.
        $relativePath = $subDirectory . '/' . $assetIdentifier;

        // Step 5: Validate that the physical file exists.
        $this->validatePhysicalFile($relativePath);

        // Step 6: Return the final, web-accessible URL for the local asset.
        return $this->baseWebPath . '/' . ltrim($relativePath, '/');
    }

    /**
     * Determines the appropriate subdirectory for a given local asset filename.
     */
    private function getSubDirectoryForAsset(string $assetFilename): string
    {
        if (str_ends_with($assetFilename, '.css')) {
            return 'css';
        }

        if (str_ends_with($assetFilename, '.js')) {
            return 'js';
        }

        throw new RuntimeException("Unsupported asset type for filename: {$assetFilename}");
    }

    /**
     * Validates that the physical asset file exists and is readable.
     */
    private function validatePhysicalFile(string $relativePath): void
    {
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        $physicalPath = $this->resourcesDirectory->path() . DIRECTORY_SEPARATOR . $normalizedPath;

        if (!is_file($physicalPath) || !is_readable($physicalPath)) {
            throw new RuntimeException("Asset source file not found or not readable: {$physicalPath}");
        }
    }
}
