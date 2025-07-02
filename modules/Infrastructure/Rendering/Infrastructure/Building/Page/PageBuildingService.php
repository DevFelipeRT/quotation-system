<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Page;

use Rendering\Application\Contract\PageBuildingServiceInterface;
use InvalidArgumentException;
use Rendering\Infrastructure\Contract\Building\Partial\FooterBuilderInterface;
use Rendering\Infrastructure\Contract\Building\Partial\HeaderBuilderInterface;
use Rendering\Infrastructure\Contract\Building\Partial\NavigationBuilderInterface;
use Rendering\Infrastructure\Contract\Building\Partial\PartialFactoryInterface;
use Rendering\Infrastructure\Contract\Building\Page\PageBuilderInterface;
use Rendering\Domain\Contract\PageInterface;
use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Partial\ValueObject\Navigation\Navigation;
use Rendering\Domain\Partial\ValueObject\Navigation\NavigationLink;
use Rendering\Domain\View\ValueObject\View;
use Rendering\Infrastructure\Contract\Building\Page\AssetPathResolverInterface;

/**
 * A high-level service that acts as a Facade for the page building process.
 *
 * This service provides a simple, procedural API for clients (like controllers)
 * to assemble a complete Page object by orchestrating a suite of specialized
 * builders for each part of the page.
 */
final class PageBuildingService implements PageBuildingServiceInterface
{
    /** @var PageBuilderInterface The core builder for the final Page object. */
    private PageBuilderInterface $pageBuilder;

    /** @var HeaderBuilderInterface A dedicated builder for the Header component. */
    private HeaderBuilderInterface $headerBuilder;

    /** @var FooterBuilderInterface A dedicated builder for the Footer component. */
    private FooterBuilderInterface $footerBuilder;

    /** @var NavigationBuilderInterface A dedicated builder for the Navigation component. */
    private NavigationBuilderInterface $navigationBuilder;

    /** @var PartialFactoryInterface A factory for creating generic partial view objects. */
    private PartialFactoryInterface $partialFactory;

    /** @var AssetPathResolverInterface An asset path resolver to convert relative paths to web-accessible URLs. */
    private AssetPathResolverInterface $assetPathResolver;

    /**
     * @param PageBuilderInterface $pageBuilder
     * @param HeaderBuilderInterface $headerBuilder
     * @param FooterBuilderInterface $footerBuilder
     * @param NavigationBuilderInterface $navigationBuilder
     * @param PartialFactoryInterface $partialFactory
     */
    public function __construct(
        PageBuilderInterface $pageBuilder,
        HeaderBuilderInterface $headerBuilder,
        FooterBuilderInterface $footerBuilder,
        NavigationBuilderInterface $navigationBuilder,
        PartialFactoryInterface $partialFactory,
        AssetPathResolverInterface $assetPathResolver
    ) {
        $this->pageBuilder = $pageBuilder;
        $this->headerBuilder = $headerBuilder;
        $this->footerBuilder = $footerBuilder;
        $this->navigationBuilder = $navigationBuilder;
        $this->partialFactory = $partialFactory;
        $this->assetPathResolver = $assetPathResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title): self
    {
        $this->headerBuilder->setTitle($title);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCopyright(string $owner, string $message = 'All rights reserved.'): self
    {
        $this->footerBuilder->setCopyright($owner, $message);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setView(string $templateFile, array $data): self
    {
        $this->pageBuilder->setView(View::create($templateFile, $data));
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNavigationLinks(array $links): self
    {
        foreach ($links as $linkData) {
            if (!isset($linkData['url'], $linkData['label'])) {
                throw new InvalidArgumentException('Each navigation link must have "url" and "label" keys.');
            }
            $link = new NavigationLink(
                $linkData['url'],
                $linkData['label'],
                $linkData['active'] ?? false
            );
            $this->navigationBuilder->addLink($link);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssets(array $assets): self
    {
        foreach ($assets as $assetPath) {
            if (!is_string($assetPath)) {
                throw new InvalidArgumentException('Assets must be a flat array of strings.');
            }
            $this->sortAsset($assetPath);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPagePartial(string $name, string $templateFile, array $data = [], array $partials = []): self
    {
        $partial = $this->createPartialView($templateFile, $data, $partials);
        $this->pageBuilder->addPartial($name, $partial);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addHeaderPartial(string $name, string $templateFile, array $data = [], array $partials = []): self
    {
        $partial = $this->createPartialView($templateFile, $data, $partials);
        $this->headerBuilder->addPartial($name, $partial);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFooterPartial(string $name, string $templateFile, array $data = [], array $partials = []): self
    {
        $partial = $this->createPartialView($templateFile, $data, $partials);
        $this->footerBuilder->addPartial($name, $partial);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addNavigationPartial(string $name, string $templateFile, array $data = [], array $partials = []): self
    {
        $partial = $this->createPartialView($templateFile, $data, $partials);
        $this->navigationBuilder->addPartial($name, $partial);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildPage(): PageInterface
    {
        // Build each component using its dedicated builder
        $navigation = $this->navigationBuilder->build() ?? null;
        $header = $this->buildHeader($navigation);
        $footer = $this->footerBuilder->build();

        // Set the final, built components on the main page builder
        $this->pageBuilder->setHeader($header);
        $this->pageBuilder->setFooter($footer);

        // Build and return the final composite Page object
        return $this->pageBuilder->build();
    }

    /**
     * Sorts a single asset path and delegates it to the appropriate builder.
     */
    private function sortAsset(string $assetPath): void
    {
        if (str_ends_with($assetPath, '.css')) {
            $resolvedPath = $this->resolveAssetPath($assetPath);
            $this->headerBuilder->addCss($resolvedPath);
        } elseif (str_ends_with($assetPath, '.js')) {
            $resolvedPath = $this->resolveAssetPath($assetPath);
            $this->footerBuilder->addJs($resolvedPath);
        }
    }

    private function createPartialView(string $templateFile, array $data, array $partials): PartialViewInterface
    {
        return $this->partialFactory->createPartialView($templateFile, $data, $partials);
    }

    private function buildHeader(?Navigation $navigation = null): PartialViewInterface
    {
        if ($navigation !== null) {
            $this->headerBuilder->addPartial('navigation', $navigation);
        }
        return $this->headerBuilder->build();
    }

    private function resolveAssetPath(string $relativePath): string
    {
        return $this->assetPathResolver->resolve($relativePath);
    }
}
