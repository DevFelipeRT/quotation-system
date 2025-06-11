<?php

declare(strict_types=1);

namespace Rendering\Application\Service;

use PublicContracts\Rendering\RenderingServiceInterface;
use Rendering\Application\Contract\ViewRendererInterface;
use Rendering\Domain\Contract\ViewDataInterface;
use Rendering\Domain\Contract\ViewInterface;
use Rendering\Domain\ValueObject\HtmlView;
use Rendering\Domain\ValueObject\ViewData;

/**
 * RenderingService
 *
 * Application Service responsible for orchestrating the rendering of
 * complete views and partials. Acts as a façade for the rendering infrastructure,
 * isolating presentation concerns from controllers and application logic.
 *
 * This service can be injected into controllers, handlers, or other services
 * that require rendered output.
 *
 * @author
 */
final class RenderingService implements RenderingServiceInterface
{
    /**
     * @var ViewRendererInterface
     */
    private ViewRendererInterface $renderer;

    /**
     * RenderingService constructor.
     *
     * @param ViewRendererInterface $renderer Concrete renderer for output (e.g., HtmlViewRenderer).
     */
    public function __construct(ViewRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Renders a full view, delivering the final string output.
     *
     * @param ViewInterface $view Immutable view value object.
     * @return string Rendered content.
     */
    public function render(ViewInterface $view): string
    {
        return $this->renderer->render($view);
    }

    /**
     * Renders a partial fragment.
     *
     * @param string $partialFileName Name of the partial file (relative to the partials directory).
     * @param array<string, mixed> $data Optional data for the partial.
     * @return string Rendered partial content.
     */
    public function renderPartial(string $partialFileName, array $data = []): string
    {
        // Ensure the renderer has the method (defensive for interface evolution)
        if (!method_exists($this->renderer, 'renderPartial')) {
            throw new \BadMethodCallException('Underlying renderer does not support partial rendering.');
        }

        // Call the renderer’s renderPartial method
        /** @var callable $callable */
        $callable = [$this->renderer, 'renderPartial'];
        return $callable($partialFileName, $data);
    }

    /**
     * Helper to create a ViewData value object.
     */
    public function createViewData(array $data = []): ViewDataInterface
    {
        return new ViewData(array_merge(['renderer' => $this->renderer], $data));
    }

    /**
     * Helper to create an HtmlView value object.
     */
    public function createHtmlView(string $fileName, string $jsFileName, array $data = []): ViewInterface
    {
        $viewData = $this->createViewData($data);
        return new HtmlView($fileName, $jsFileName, $viewData);
    }
}
