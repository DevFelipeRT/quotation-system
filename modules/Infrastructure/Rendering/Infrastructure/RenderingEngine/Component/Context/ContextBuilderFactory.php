<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component\Context;

use Rendering\Domain\Contract\Page\PageInterface;
use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Infrastructure\RenderingEngine\Component\Context\Builder\PageContextBuilder;
use Rendering\Infrastructure\RenderingEngine\Component\Context\Builder\PartialContextBuilder;
use Rendering\Infrastructure\RenderingEngine\Component\Context\Builder\ViewContextBuilder;

/**
 * Factory responsible for creating a fully configured ContextBuilder orchestrator.
 *
 * This class encapsulates the logic of assembling the dispatch map of specialized
 * context builders, keeping the service container or bootstrap file clean.
 */
final class ContextBuilderFactory
{
    /**
     * Creates and configures the ContextBuilder orchestrator with all its specialist dependencies.
     */
    public function create(): ComponentContextBuilder
    {
        // Build the dispatch map. The key is the domain interface, and the value
        // is the specialist context builder instance responsible for it.
        $specialistBuilders = [
            PageInterface::class => new PageContextBuilder(),
            ViewInterface::class => new ViewContextBuilder(),
            PartialViewInterface::class => new PartialContextBuilder(),
        ];

        return new ComponentContextBuilder($specialistBuilders);
    }
}
