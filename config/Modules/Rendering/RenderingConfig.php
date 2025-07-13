<?php

declare(strict_types=1);

namespace Config\Modules\Rendering;

use PublicContracts\Rendering\RenderingConfigInterface;

/**
 * A concrete implementation of the rendering configuration.
 *
 * This class holds all configuration settings required by the rendering module,
 * including paths and copyright information.
 */
final class RenderingConfig implements RenderingConfigInterface
{
    /** Default values for rendering configuration. */
    private const DEFAULT_VALUES = [
        'copyrightOwner' => 'My Company',
        'copyrightMessage' => 'All rights reserved.',
    ];

    /** The absolute path to the views directory. */
    private readonly string $viewsDirectory;

    /** The absolute path to the cache directory. */
    private readonly string $cacheDirectory;

    /** The absolute path to the directory containing static assets (CSS, JS, images). */
    private readonly string $assetsDirectory;

    /** The copyright owner name. */
    private readonly string $copyrightOwner;

    /** The copyright message. */
    private readonly string $copyrightMessage;

    /**
     * @param string $viewsDirectory The absolute path to the views directory.
     * @param string $cacheDirectory The absolute path to the cache directory.
     * @param string $assetsDirectory The absolute path to the assets directory.
     * @param string|null $copyrightOwner The copyright owner name (defaults to DEFAULT_VALUES).
     * @param string|null $copyrightMessage The copyright message (defaults to DEFAULT_VALUES).
     */
    public function __construct(
        string $viewsDirectory,
        string $cacheDirectory,
        string $assetsDirectory,
        ?string $copyrightOwner = null,
        ?string $copyrightMessage = null
    ) {
        $this->viewsDirectory = $viewsDirectory;
        $this->cacheDirectory = $cacheDirectory;
        $this->assetsDirectory = $assetsDirectory;
        $this->copyrightOwner = $copyrightOwner ?? self::DEFAULT_VALUES['copyrightOwner'];
        $this->copyrightMessage = $copyrightMessage ?? self::DEFAULT_VALUES['copyrightMessage'];
    }

    /**
     * {@inheritdoc}
     */
    public function viewsDirectory(): string
    {
        return $this->viewsDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheDirectory(): string
    {
        return $this->cacheDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function assetsDirectory(): string
    {
        return $this->assetsDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function copyrightOwner(): string
    {
        return $this->copyrightOwner;
    }

    /**
     * {@inheritdoc}
     */
    public function copyrightMessage(): string
    {
        return $this->copyrightMessage;
    }
}