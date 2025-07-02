<?php

declare(strict_types=1);

namespace PublicContracts\Rendering;

/**
 * Defines the contract for a rendering configuration object.
 *
 * This interface ensures that any configuration object provides the necessary
 * methods for retrieving critical settings for the rendering module.
 */
interface RenderingConfigInterface
{
    /**
     * Returns the absolute path to the directory containing view files.
     *
     * @return string
     */
    public function viewsDirectory(): string;

    /**
     * Returns the absolute path to the directory where compiled templates are cached.
     *
     * @return string
     */
    public function cacheDirectory(): string;

    /**
     * Returns the absolute path to the directory containing static assets (CSS, JS, images).
     *
     * @return string
     */
    public function assetsDirectory(): string;

    /**
     * Returns the name of the copyright holder (e.g., "My Company Inc.").
     *
     * @return string
     */
    public function copyrightOwner(): string;

    /**
     * Returns the copyright message (e.g., "All rights reserved.").
     *
     * @return string
     */
    public function copyrightMessage(): string;
}