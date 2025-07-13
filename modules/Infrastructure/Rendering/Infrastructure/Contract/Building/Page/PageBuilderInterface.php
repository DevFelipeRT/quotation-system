<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Page;

use Rendering\Infrastructure\Contract\Building\RenderableBuilderInterface;

use Rendering\Domain\Contract\Page\AssetsInterface;
use Rendering\Domain\Contract\Page\PageInterface;
use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Domain\ValueObject\Partial\Footer;
use Rendering\Domain\ValueObject\Partial\Header;

/**
 * Defines the contract for a Page Builder.
 *
 * The builder pattern is used to encapsulate the complex, multi-step process
 * of constructing a complete Page object. This interface provides a fluent API
 * for the client (e.g., a Controller) to assemble a page step-by-step.
 */
interface PageBuilderInterface extends RenderableBuilderInterface
{
    /**
     * Sets the layout template for the page.
     *
     * This method allows the builder to specify which layout file should be used
     * to render the page, providing a structure for the overall page design.
     *
     * @param string $layout The path to the layout template file.
     * @return self
     */
    public function setLayout(string $layout): self;

    /**
     * Sets the primary view component for the page.
     *
     * @param ViewInterface $view The main content view object.
     * @return self
     */
    public function setView(ViewInterface $view): self;

    /**
     * Sets the assets (CSS/JS) for the page.
     *
     * This method allows the builder to specify which assets should be included
     * in the page, such as stylesheets and JavaScript files.
     *
     * @param array $assets An array of asset paths.
     * @return self
     */
    public function setAssets(array $assets): self;

    /**
     * Sets the header component for the page.
     *
     * @param Header $header The header component.
     * @return self
     */
    public function setHeader(Header $header): self;

    /**
     * Sets the footer component for the page.
     *
     * @param Footer $footer The footer component.
     * @return self
     */
    public function setFooter(Footer $footer): self;

    /**
     * Assembles all the provided parts into a final, immutable Page object.
     *
     * @return PageInterface The fully constructed, composite Page object.
     * @throws LogicException If essential parts (like the view) are missing before building.
     */
    public function build(): PageInterface;
}