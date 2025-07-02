<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Page;

use Rendering\Domain\Contract\PageInterface;
use Rendering\Domain\Contract\ViewInterface;
use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Partial\ValueObject\Header;
use Rendering\Domain\Partial\ValueObject\Footer;

/**
 * Defines the contract for a Page Builder.
 *
 * The builder pattern is used to encapsulate the complex, multi-step process
 * of constructing a complete Page object. This interface provides a fluent API
 * for the client (e.g., a Controller) to assemble a page step-by-step.
 */
interface PageBuilderInterface
{
    /**
     * Sets the header component for the page.
     *
     * @param Header $header The header component.
     * @return self
     */
    public function setHeader(Header $header): self;

    /**
     * Sets the primary view component for the page.
     *
     * @param ViewInterface $view The main content view object.
     * @return self
     */
    public function setView(ViewInterface $view): self;

    /**
     * Sets the footer component for the page.
     *
     * @param Footer $footer The footer component.
     * @return self
     */
    public function setFooter(Footer $footer): self;

    /**
     * Sets the injectable partial views for the page.
     *
     * These partials can be rendered from within the primary view's template
     * using a directive like @partial('identifier').
     *
     * @param array<string, PartialViewInterface> $partials An associative array of partials, keyed by their identifier.
     * @return self
     */
    public function setPartials(array $partials): self;

    /**
     * Adds a single injectable partial view to the page.
     *
     * This allows for dynamic addition of partials that can be rendered
     * within the primary view's template.
     *
     * @param string $key The identifier for the partial.
     * @param PartialViewInterface $partial The partial view object to add.
     * @return self
     */
    public function addPartial(string $key, PartialViewInterface $partial): self;

    /**
     * Assembles all the provided parts into a final, immutable Page object.
     *
     * @return PageInterface The fully constructed, composite Page object.
     * @throws \LogicException If essential parts (like the view) are missing before building.
     */
    public function build(): PageInterface;
}