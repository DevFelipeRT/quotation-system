<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Factory;

use InvalidArgumentException;
use Rendering\Domain\Contract\Page\AssetsInterface;
use Rendering\Domain\ValueObject\Page\Assets;

/**
 * Factory responsible for creating Assets instances from various data sources.
 * 
 * This factory provides methods to construct Assets value objects from arrays,
 * individual parameters, or by merging multiple assets sources, following the
 * project's established factory pattern.
 */
final class AssetsFactory
{
    private const SUPPORTED_EXTENSIONS = ['.css', '.js'];
    private const CSS_EXTENSION = '.css';
    private const JS_EXTENSION = '.js';

    /**
     * Creates an Assets instance from an array, intelligently detecting its structure.
     * 
     * Supports multiple array structures:
     * 1. Associative: ['css' => ['style.css'], 'js' => ['app.js']]
     * 2. Flat array: ['style.css', 'app.js', 'theme.css']
     * 3. Mixed arrays containing AssetsInterface instances
     * 4. Arrays with AssetsInterface instances mixed with file paths or other structures
     *
     * @param array $data The asset data array
     * @return AssetsInterface The constructed Assets object
     * @throws InvalidArgumentException If the array structure is invalid
     */
    public function createFromArray(array $data): AssetsInterface
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Asset data array cannot be empty.');
        }

        // Extract asset instances first for efficiency
        [$assetInstances, $remainingData] = $this->separateAssetInstances($data);
        
        $baseAssets = $this->createBaseAssetsFromData($remainingData);

        return $this->combineAssets($assetInstances, $baseAssets);
    }

    /**
     * Creates an Assets instance from an associative array.
     *
     * Expected array structure:
     * [
     *     'css' => ['style.css', 'theme.css'],
     *     'js' => ['app.js', 'utils.js']
     * ]
     *
     * @param array<string, string[]> $data The asset data array
     * @return AssetsInterface The constructed Assets object
     * @throws InvalidArgumentException If the array structure is invalid
     */
    public function createFromAssociativeArray(array $data): AssetsInterface
    {
        $css = $data['css'] ?? [];
        $js = $data['js'] ?? [];

        $this->validateAssetArray($css, 'CSS');
        $this->validateAssetArray($js, 'JavaScript');

        return new Assets($css, $js);
    }

    /**
     * Creates an Assets instance with separate CSS and JavaScript arrays.
     *
     * @param string[] $css Array of CSS file paths
     * @param string[] $js Array of JavaScript file paths
     * @return AssetsInterface The constructed Assets object
     */
    public function createFromSeparateArrays(array $css = [], array $js = []): AssetsInterface
    {
        return new Assets($css, $js);
    }

    /**
     * Creates an Assets instance from a flat array of asset paths.
     * Files are automatically sorted by extension.
     *
     * @param string[] $assetPaths Array of asset file paths
     * @return AssetsInterface The constructed Assets object
     * @throws InvalidArgumentException If any path is invalid
     */
    public function createFromFlatArray(array $assetPaths): AssetsInterface
    {
        $css = [];
        $js = [];

        foreach ($assetPaths as $path) {
            $trimmedPath = $this->validateAndNormalizePath($path);
            
            if (str_ends_with($trimmedPath, self::CSS_EXTENSION)) {
                $css[] = $trimmedPath;
            } elseif (str_ends_with($trimmedPath, self::JS_EXTENSION)) {
                $js[] = $trimmedPath;
            } else {
                throw new InvalidArgumentException("Unsupported asset type for file: {$path}");
            }
        }

        return new Assets($css, $js);
    }

    /**
     * Merges multiple Assets instances into a single one.
     * Removes duplicates while preserving order.
     *
     * @param AssetsInterface ...$assets Multiple Assets instances to merge
     * @return AssetsInterface The merged Assets object
     */
    public function merge(AssetsInterface ...$assets): AssetsInterface
    {
        if (count($assets) === 1) {
            return $assets[0];
        }

        $allCss = [];
        $allJs = [];

        foreach ($assets as $asset) {
            array_push($allCss, ...$asset->cssLinks());
            array_push($allJs, ...$asset->jsLinks());
        }

        return new Assets(
            array_values(array_unique($allCss)),
            array_values(array_unique($allJs))
        );
    }

    /**
     * Validates and normalizes an asset path.
     *
     * @param mixed $path The path to validate
     * @return string The normalized path
     * @throws InvalidArgumentException If the path is invalid
     */
    private function validateAndNormalizePath(mixed $path): string
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('All asset paths must be strings.');
        }

        $trimmedPath = trim($path);
        if ($trimmedPath === '') {
            throw new InvalidArgumentException('Asset paths cannot be empty.');
        }

        return $trimmedPath;
    }

    /**
     * Validates that an asset array contains only valid paths.
     *
     * @param array $assets The array to validate
     * @param string $type The type name for error messages
     * @throws InvalidArgumentException If validation fails
     */
    private function validateAssetArray(array $assets, string $type): void
    {
        foreach ($assets as $index => $asset) {
            try {
                $this->validateAndNormalizePath($asset);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(
                    "{$type} asset at index {$index}: {$e->getMessage()}"
                );
            }
        }
    }

    /**
     * Separates AssetInterface instances from other data in a single pass.
     *
     * @param array $data The array to process
     * @return array{0: AssetsInterface[], 1: array} [asset instances, remaining data]
     */
    private function separateAssetInstances(array $data): array
    {
        $assetInstances = [];
        $remainingData = [];

        foreach ($data as $key => $item) {
            if ($item instanceof AssetsInterface) {
                $assetInstances[] = $item;
            } else {
                $remainingData[$key] = $item;
            }
        }

        return [$assetInstances, $remainingData];
    }

    /**
     * Creates base Assets from data, detecting structure automatically.
     *
     * @param array $data The data to process
     * @return AssetsInterface|null The created Assets instance or null
     */
    private function createBaseAssetsFromData(array $data): ?AssetsInterface
    {
        if (empty($data)) {
            return null;
        }
        if ($this->isAssociativeAssetArray($data)) {
            return $this->createFromAssociativeArray($data);
        }
        if ($this->isFlatAssetArray($data)) {
            return $this->createFromFlatArray($data);
        }

        return null;
    }

    /**
     * Combines asset instances with base assets efficiently.
     *
     * @param AssetsInterface[] $assetInstances
     * @param AssetsInterface|null $baseAssets
     * @return AssetsInterface
     * @throws InvalidArgumentException If no valid assets found
     */
    private function combineAssets(array $assetInstances, ?AssetsInterface $baseAssets): AssetsInterface
    {
        if (!empty($assetInstances)) {
            if ($baseAssets !== null) {
                return $this->merge($baseAssets, ...$assetInstances);
            }
            return $this->merge(...$assetInstances);
        }
        if ($baseAssets !== null) {
            return $baseAssets;
        }

        throw new InvalidArgumentException(
            'Array structure not recognized. Expected associative array with css/js keys, flat array of file paths, or arrays containing AssetsInterface instances.'
        );
    }

    /**
     * Checks if the array is an associative array with css/js keys.
     * Optimized to check structure without unnecessary iterations.
     *
     * @param array $data The array to check
     * @return bool True if it's an associative asset array
     */
    private function isAssociativeAssetArray(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $hasCssKey = isset($data['css']) && is_array($data['css']);
        $hasJsKey = isset($data['js']) && is_array($data['js']);
        
        return $hasCssKey || $hasJsKey;
    }

    /**
     * Checks if the array is a flat array of file paths.
     * Uses early exit for better performance.
     *
     * @param array $data The array to check
     * @return bool True if it's a flat asset array
     */
    private function isFlatAssetArray(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        if (!array_is_list($data)) {
            return false;
        }
        foreach ($data as $value) {
            if (!is_string($value) || trim($value) === '') {
                return false;
            }
            
            if (!$this->hasSupportedExtension($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if a file path has a supported extension.
     *
     * @param string $path The file path to check
     * @return bool True if the extension is supported
     */
    private function hasSupportedExtension(string $path): bool
    {
        foreach (self::SUPPORTED_EXTENSIONS as $extension) {
            if (str_ends_with($path, $extension)) {
                return true;
            }
        }
        return false;
    }

}

