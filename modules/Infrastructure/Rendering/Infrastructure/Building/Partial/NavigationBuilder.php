<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Partial;

use Rendering\Domain\Contract\Partial\Navigation\NavigationInterface;
use Rendering\Domain\ValueObject\Partial\Navigation\Navigation;
use Rendering\Domain\ValueObject\Partial\Navigation\NavigationLink;
use Rendering\Domain\ValueObject\Partial\Navigation\NavigationLinkCollection;
use Rendering\Infrastructure\Building\Factory\NavigationLinkFactory;
use Rendering\Infrastructure\Building\Factory\PartialFactory;
use Rendering\Infrastructure\Building\Factory\RenderableDataFactory;
use Rendering\Infrastructure\Contract\Building\Partial\NavigationBuilderInterface;

/**
 * Implements the Builder pattern to assemble a complete Navigation object.
 */
final class NavigationBuilder extends PartialBuilder implements NavigationBuilderInterface
{
    private const DEFAULT_TEMPLATE = 'partial/navigation.phtml';
    private NavigationLinkFactory $linkFactory;

    /**
     * @var NavigationLink[]
     */
    private array $links = [];

    /**
     * Constructor to initialize the NavigationBuilder with necessary factories.
     * Sets the default template file for the navigation.
     * Template files can be overridden by calling setTemplateFile().
     *
     * @param PartialFactory $partialFactory Factory to create partial views.
     * @param RenderableDataFactory $dataFactory Factory to create renderable data.
     * @param NavigationLinkFactory $linkFactory Factory to create navigation links.
     */
    public function __construct(
        PartialFactory $partialFactory, 
        RenderableDataFactory $dataFactory,
        NavigationLinkFactory $linkFactory
    ) {
        parent::__construct($partialFactory, $dataFactory);
        $this->linkFactory = $linkFactory;
        $this->initializeTemplateFile(self::DEFAULT_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function addLink(string $label, string $url, bool $isActive = false): self
    {
        $link = $this->linkFactory->createNavigationLink($label, $url, $isActive);
        $this->links[] = $link;
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLinks(array $links): self
    {
        $this->links = [];
        foreach ($links as $link) {
            $link = $this->linkFactory->createNavigationLinkFromArray($link);
            $this->links[] = $link;
        }
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isReady(): bool
    {
        return $this->isConfigured && !empty($this->links) && !empty($this->templateFile);
    }

    /**
     * {@inheritdoc}
     */
    public function build(): NavigationInterface
    {
        if (empty($this->links) || empty($this->templateFile)) {
            throw new \LogicException('NavigationBuilder is not ready to build. Ensure links and template file are set.');
        }
        return new Navigation(
            $this->templateFile,
            $this->buildLinksCollection(),
            $this->buildDataFromArray($this->data),
            $this->buildPartialsCollection($this->partials),
        );
    }

    /**
     * Builds a NavigationLinkCollection from the links array.
     *
     * @return NavigationLinkCollection
     */
    private function buildLinksCollection(): NavigationLinkCollection
    {
        return $this->linkFactory->createNavigationLinkCollection($this->links);
    }
}
