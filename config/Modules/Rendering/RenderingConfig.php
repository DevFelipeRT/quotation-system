<?php

declare(strict_types=1);

namespace Config\Modules\Rendering;

use PublicContracts\Rendering\RenderingConfigInterface;

/**
 * A concrete implementation of the rendering configuration.
 *
 * This class holds the path settings required by the rendering module.
 */
final class RenderingConfig implements RenderingConfigInterface
{
    /**
     * @var string The absolute path to the views directory.
     */
    private readonly string $viewsDirectory;

    /**
     * @var string The absolute path to the cache directory.
     */
    private readonly string $cacheDirectory;

    /**
     * @var string The absolute path to the directory containing static assets (CSS, JS, images).
     */
    private readonly string $assetsDirectory;

    /**
     * @param string $viewsDirectory The absolute path to the views directory.
     * @param string $cacheDirectory The absolute path to the cache directory.
     */
    public function __construct(
        string $viewsDirectory,
        string $cacheDirectory,
        string $assetsDirectory
    ) {
        $this->viewsDirectory = $viewsDirectory;
        $this->cacheDirectory = $cacheDirectory;
        $this->assetsDirectory = $assetsDirectory;

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
        return RenderingDefaultValues::COPYRIGHT_OWNER->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function copyrightMessage(): string
    {
        return RenderingDefaultValues::COPYRIGHT_MESSAGE->getValue();
    }
}