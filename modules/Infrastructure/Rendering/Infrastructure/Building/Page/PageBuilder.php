<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Page;

use Rendering\Infrastructure\Contract\Building\Page\PageBuilderInterface;
use LogicException;
use Rendering\Domain\Contract\PageInterface;
use Rendering\Domain\Contract\ViewInterface;
use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Page\ValueObject\Page;
use Rendering\Domain\Partial\ValueObject\Header;
use Rendering\Domain\Partial\ValueObject\Footer;

/**
 * Implements the Builder pattern to assemble a complete Page object.
 *
 * This builder provides a fluent API to construct a complex Page object
 * step-by-step. It simplifies the page creation process for the client by
 * encapsulating the assembly of all required components.
 */
final class PageBuilder implements PageBuilderInterface
{
    /**
     * The header component for the page.
     * @var Header|null
     */
    private ?Header $header = null;

    /**
     * The main view component for the page.
     * @var ViewInterface|null
     */
    private ?ViewInterface $view = null;

    /**
     * The footer component for the page.
     * @var Footer|null
     */
    private ?Footer $footer = null;

    /**
     * An associative array of injectable partial views.
     * @var array<string, PartialViewInterface>
     */
    private array $partials = [];

    /**
     * {@inheritdoc}
     */
    public function setHeader(Header $header): self
    {
        $this->header = $header;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setView(ViewInterface $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFooter(Footer $footer): self
    {
        $this->footer = $footer;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPartials(array $partials): self
    {
        $this->partials = $partials;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPartial(string $name, PartialViewInterface $partial): self
    {
        if (isset($this->partials[$name])) {
            throw new LogicException("Partial with name '{$name}' already exists.");
        }
        
        $this->partials[$name] = $partial;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function build(): PageInterface
    {
        if ($this->header === null) {
            throw new LogicException('Cannot build a page without a header component.');
        }
        
        if ($this->view === null) {
            throw new LogicException('Cannot build a page without a view component.');
        }

        if ($this->footer === null) {
            throw new LogicException('Cannot build a page without a footer component.');
        }

        return new Page(
            header:     $this->header,
            view:       $this->view,
            footer:     $this->footer,
            partials:   $this->partials
        );
    }
}
