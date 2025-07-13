<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract\Page;

use Rendering\Domain\Contract\RenderableDataInterface;

/**
 * Contract for an immutable value object representing page assets (CSS and JS).
 *
 * Extends RenderableDataInterface to ensure compatibility with view rendering systems
 * while providing specific methods for accessing asset collections.
 */
interface AssetsInterface extends RenderableDataInterface
{
    /**
     * Returns an array of CSS file paths.
     *
     * @return string[]
     */
    public function cssLinks(): array;

    /**
     * Returns an array of JavaScript file paths.
     *
     * @return string[]
     */
    public function jsLinks(): array;

    /**
     * Checks if a specific asset path exists in either CSS or JS collections.
     *
     * @param string $path The asset path to check.
     * @return bool True if the asset path exists, false otherwise.
     */
    public function hasLink(string $path): bool;
}
