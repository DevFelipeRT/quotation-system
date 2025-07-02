<?php

declare(strict_types=1);

namespace Rendering\Domain\Page\ValueObject;

use Rendering\Domain\Contract\PageInterface;
use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Contract\ViewInterface;
use Rendering\Domain\Partial\ValueObject\Footer;
use Rendering\Domain\Partial\ValueObject\Header;
use Rendering\Domain\Partial\ValueObject\Navigation\Navigation;

/**
 * A composite Value Object that represents a complete, renderable page.
 *
 * It encapsulates all the constituent parts of a page (header, view, footer, etc.)
 * into a single, cohesive, and immutable unit.
 */
final class Page implements PageInterface
{
    /**
     * @param Header $header The page's header component.
     * @param ViewInterface $view The page's main view content component.
     * @param Footer $footer The page's footer component.
     * @param array<string, PartialViewInterface> $partials An associative array of partials to be injected.
     */
    public function __construct(
        private readonly Header $header,
        private readonly ViewInterface $view,
        private readonly Footer $footer,
        private readonly array $partials = []
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function header(): Header
    {
        return $this->header;
    }

    /**
     * {@inheritdoc}
     */
    public function view(): ViewInterface
    {
        return $this->view;
    }

    /**
     * {@inheritdoc}
     */
    public function footer(): Footer
    {
        return $this->footer;
    }

    /**
     * {@inheritdoc}
     */
    public function partials(): array
    {
        return $this->partials;
    }
}