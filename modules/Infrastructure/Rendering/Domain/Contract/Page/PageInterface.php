<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract\Page;

use Rendering\Domain\Contract\PartialProviderInterface;
use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Domain\ValueObject\Partial\Footer;
use Rendering\Domain\ValueObject\Partial\Header;

/**
 * Defines the contract for a composite Page object.
 *
 * A Page represents the entire renderable document, aggregating all its
 * structural parts. By extending PartialProviderInterface, it also acts

 * as the primary context for providing injectable partial view components.
 */
interface PageInterface extends RenderableInterface, PartialProviderInterface
{
    /**
     * Returns the title of the page.
     */
    public function title(): string;
    
    /**
     * Returns the header component of the page, if available.
     */
    public function header(): ?Header;

    /**
     * Returns the primary view content of the page.
     */
    public function view(): ViewInterface;

    /**
     * Returns the footer component of the page, if available.
     */
    public function footer(): ?Footer;

    /**
     * Returns the assets (CSS/JS) associated with the page.
     */
    public function assets(): AssetsInterface;
}
