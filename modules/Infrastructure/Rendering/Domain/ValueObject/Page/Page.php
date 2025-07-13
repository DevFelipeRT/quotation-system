<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject\Page;

use Rendering\Domain\ValueObject\Shared\Renderable;
use Rendering\Domain\Contract\Page\PageInterface;
use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Domain\Contract\Page\AssetsInterface;
use Rendering\Domain\Contract\RenderableDataInterface;
use Rendering\Domain\ValueObject\Partial\Footer;
use Rendering\Domain\ValueObject\Partial\Header;
use Rendering\Domain\ValueObject\Shared\PartialsCollection;

/**
 * A composite Value Object that represents a complete, renderable page.
 *
 * It encapsulates all the constituent parts of a page (header, view, footer, etc.)
 * into a single, cohesive, and immutable unit.
 */
final class Page extends Renderable implements PageInterface
{
    private readonly string $title;
    private readonly ViewInterface $view;
    private readonly ?AssetsInterface $assets;
    private readonly ?Header $header;
    private readonly ?Footer $footer;

    /**
     * Constructs a new instance of the Page Value Object.
     *
     * @param string                       $layout   The layout template file for the page.
     * @param ViewInterface                $view     The main view component of the page.
     * @param RenderableDataInterface|null $data     An optional data provider for the page.
     * @param AssetsInterface|null         $assets   The assets (CSS/JS) associated with the page, or null if none.
     * @param Header|null                  $header   The header component of the page, or null if not provided.
     * @param Footer|null                  $footer   The footer component of the page, or null if not provided.
     * @param PartialsCollection|null      $partials An optional collection of partials to be injected into the page.
     */
    public function __construct(
        string $layout,
        ViewInterface $view,
        ?RenderableDataInterface $data = null,
        ?AssetsInterface $assets = null,
        ?Header $header = null,
        ?Footer $footer = null,
        ?PartialsCollection $partials = null
    ) {
        $this->title = $view->title();
        $this->view = $view;
        $this->assets = $assets;
        $this->header = $header;
        $this->footer = $footer;
        parent::__construct(
            $layout, 
            $data,
            $partials
        );
    }

    /**
     * {@inheritdoc}
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function header(): ?Header
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
    public function footer(): ?Footer
    {
        return $this->footer;
    }

    /**
     * {@inheritdoc}
     */
    public function assets(): AssetsInterface
    {
        return $this->assets;
    }
}