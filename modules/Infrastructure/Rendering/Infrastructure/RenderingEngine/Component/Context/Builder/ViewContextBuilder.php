<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component\Context\Builder;

use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\ContextBuilderInterface;
use InvalidArgumentException;
use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\RenderContextInterface;
use Rendering\Infrastructure\RenderingEngine\Component\Context\RenderContext;

/**
 * A specialized context builder for objects that implement ViewInterface.
 */
final class ViewContextBuilder implements ContextBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(RenderableInterface $renderable): RenderContextInterface
    {
        if (!$renderable instanceof ViewInterface) {
            throw new InvalidArgumentException('ViewContextBuilder only supports instances of ViewInterface.');
        }

        $data = $renderable->data()?->all() ?? [];
        
        // For a standalone View, the API context is the View itself.
        return new RenderContext($data, $renderable);
    }
}
