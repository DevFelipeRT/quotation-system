<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Page;

use RuntimeException;

/**
 * Defines the contract for a service that resolves relative asset paths
 * into public, web-accessible URLs.
 */
interface AssetPathResolverInterface
{
    /**
     * Resolves a relative asset path into a full, web-accessible URL.
     *
     * This method should also verify that the corresponding physical file exists.
     *
     * @param string $relativePath The relative path of the asset (e.g., 'css/style.css').
     * @return string The web-accessible URL for the asset (e.g., '/resources/css/style.css').
     * @throws RuntimeException if the asset file is not found or is not readable.
     */
    public function resolve(string $relativePath): string;
}
