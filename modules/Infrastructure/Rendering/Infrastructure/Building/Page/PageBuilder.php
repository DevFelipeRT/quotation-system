<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Page;

use Rendering\Infrastructure\Building\AbstractRenderableBuilder;
use Rendering\Infrastructure\Contract\Building\Page\PageBuilderInterface;
use LogicException;
use Rendering\Domain\Contract\Page\AssetsInterface;
use Rendering\Domain\Contract\Page\PageInterface;
use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Domain\ValueObject\Page\Page;
use Rendering\Domain\ValueObject\Partial\Footer;
use Rendering\Domain\ValueObject\Partial\Header;
use Rendering\Infrastructure\Building\Factory\AssetsFactory;
use Rendering\Infrastructure\Building\Factory\PartialFactory;
use Rendering\Infrastructure\Building\Factory\RenderableDataFactory;

/**
 * Implements the Builder pattern to assemble a complete Page object.
 *
 * This builder provides a fluent API to construct a complex Page object
 * step-by-step. It simplifies the page creation process for the client by
 * encapsulating the assembly of all required components.
 */
final class PageBuilder extends AbstractRenderableBuilder implements PageBuilderInterface
{
    private const DEFAULT_LAYOUT = 'layout/main-layout.phtml';

    private AssetsFactory $assetsFactory;

    /**
     * The main view component for the page.
     * Must be set before building the page.
     * @var ViewInterface|null
     */
    private ?ViewInterface $view = null;

    /**
     * The assets (CSS/JS) associated with the page.
     * @var AssetsInterface|null
     */
    private ?AssetsInterface $assets = null;

    /**
     * The header component for the page.
     * @var Header|null
     */
    private ?Header $header = null;

    /**
     * The footer component for the page.
     * @var Footer|null
     */
    private ?Footer $footer = null;

    /**
     * Constructor to initialize the PageBuilder with necessary factories.
     * Sets the default layout template file.
     *
     * @param PartialFactory $partialFactory Factory to create partial views.
     * @param RenderableDataFactory $dataFactory Factory to create renderable data.
     * @param AssetsFactory $assetsFactory Factory to create assets.
     */
    public function __construct(
        PartialFactory $partialFactory,
        RenderableDataFactory $dataFactory,
        AssetsFactory $assetsFactory
    ) {
        parent::__construct($partialFactory, $dataFactory);
        $this->assetsFactory = $assetsFactory;
        $this->initializeTemplateFile(self::DEFAULT_LAYOUT);
    }

    /**
     * {@inheritdoc}
     */
    public function setLayout(string $layout): self
    {
        $this->setTemplateFile($layout);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setView(ViewInterface $view): self
    {
        $this->view = $view;
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssets(array $assets): self
    {
        $this->assets = $this->assetsFactory->createFromArray($assets);
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader(Header $header): self
    {
        $this->header = $header;
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFooter(Footer $footer): self
    {
        $this->footer = $footer;
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function build(): PageInterface
    {
        $this->checkBuildState();
        return new Page(
            layout:   $this->templateFile,
            view:     $this->view,
            data:     $this->buildDataFromArray($this->data),
            assets:   $this->assets,
            header:   $this->header,
            footer:   $this->footer,
            partials: $this->buildPartialsCollection($this->partials)
        );
    }

    /**
     * Validates the current state of the builder before building the page.
     *
     * Ensures that all required components are set and initializes defaults
     * where necessary. Throws an exception if any required component is missing.
     *
     * @throws LogicException If the view or assets are not set.
     */
    private function checkBuildState(): void
    {
        if ($this->view === null) {
            throw new LogicException('View must be set before building the page.');
        }
    }
}
