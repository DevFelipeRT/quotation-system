<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Page;

/**
 * Defines the contract for a mutable service that collects assets.
 *
 * This interface standardizes the API for adding and retrieving CSS and
 * JavaScript file paths during the page assembly process.
 */
interface AssetCollectionInterface
{
    /**
     * Registers a CSS file path to the collection.
     *
     * Implementations should handle duplicates gracefully (e.g., by ignoring them).
     *
     * @param string $path The public path to the CSS file.
     */
    public function addCss(string $path): void;

    /**
     * Registers a JavaScript file path to the collection.
     *
     * Implementations should handle duplicates gracefully (e.g., by ignoring them).
     *
     * @param string $path The public path to the JS file.
     */
    public function addJs(string $path): void;

    /**
     * Retrieves all registered CSS file paths.
     *
     * @return string[]
     */
    public function getCssFiles(): array;

    /**
     * Retrieves all registered JavaScript file paths.
     *
     * @return string[]
     */
    public function getJsFiles(): array;
}