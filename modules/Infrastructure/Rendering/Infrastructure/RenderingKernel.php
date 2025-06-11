<?php

declare(strict_types=1);

namespace Rendering\Infrastructure;

use PublicContracts\Rendering\RenderingServiceInterface;
use Rendering\Application\Service\RenderingService;

/**
 * RenderingKernel
 *
 * Responsible solely for instantiating all rendering module components,
 * with their dependencies pre-wired. Does not interact with any container.
 * Exposes ready-to-use service and renderer instances.
 *
 * @author
 */
final class RenderingKernel
{
    /**
     * @var HtmlViewRenderer
     */
    private HtmlViewRenderer $renderer;

    /**
     * @var RenderingServiceInterface
     */
    private RenderingServiceInterface $renderingService;

    /**
     * Instantiates all rendering components with their dependencies.
     *
     * @param string $viewsDirectory    Absolute path to the views directory.
     * @param string $partialsDirectory Absolute path to the partials directory.
     */
    public function __construct(string $viewsDirectory, string $partialsDirectory)
    {
        $this->renderer = new HtmlViewRenderer($partialsDirectory, $viewsDirectory);
        $this->renderingService = new RenderingService($this->renderer);
    }

    /**
     * Returns the ready-to-use renderer.
     *
     * @return HtmlViewRenderer
     */
    public function renderer(): HtmlViewRenderer
    {
        return $this->renderer;
    }

    /**
     * Returns the ready-to-use rendering service.
     *
     * @return RenderingServiceInterface
     */
    public function renderingService(): RenderingServiceInterface
    {
        return $this->renderingService;
    }
}
