<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component\Renderer;

use Rendering\Domain\Contract\Page\PageInterface;
use Rendering\Domain\Contract\RenderableInterface;
use InvalidArgumentException;

/**
 * A specialized renderer for objects that implement PageInterface.
 *
 * This class leverages the common rendering logic from AbstractComponentRenderer
 * and specifies that the actual template to be rendered is the one associated
 * with the Page's View object.
 */
final class PageRenderer extends AbstractComponentRenderer
{
    /**
     * {@inheritdoc}
     *
     * For a Page, the actual template to render is its associated View. The context,
     * however, is built from the Page itself to include all necessary data and partials.
     */
    protected function getTemplateTarget(RenderableInterface $renderable): RenderableInterface
    {
        if (!$renderable instanceof PageInterface) {
            throw new InvalidArgumentException('PageRenderer only supports instances of PageInterface.');
        }
        
        return $renderable->view();
    }
}
