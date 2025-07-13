<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building;

use InvalidArgumentException;
use Rendering\Domain\Contract\Page\PageInterface;
use Rendering\Domain\Contract\Partial\Navigation\NavigationInterface;
use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Domain\Contract\Service\BuildingServiceInterface;
use Rendering\Infrastructure\Contract\Building\Page\AssetPathResolverInterface;
use Rendering\Infrastructure\Contract\Building\Page\PageBuilderInterface;
use Rendering\Infrastructure\Contract\Building\Partial\FooterBuilderInterface;
use Rendering\Infrastructure\Contract\Building\Partial\HeaderBuilderInterface;
use Rendering\Infrastructure\Contract\Building\Partial\NavigationBuilderInterface;
use Rendering\Infrastructure\Contract\Building\Partial\PartialBuilderInterface;
use Rendering\Infrastructure\Contract\Building\View\ViewBuilderInterface;

/**
 * A high-level service that acts as a Facade for the page building process.
 *
 * This service provides a simple, procedural API for clients (like controllers)
 * to assemble a complete Page object by orchestrating a suite of specialized
 * builders for each part of the page.
 */
final class BuildingService implements BuildingServiceInterface
{
    /** @var PageBuilderInterface The core builder for the final Page object. */
    private PageBuilderInterface $pageBuilder;

    /** @var ViewBuilderInterface A dedicated builder for the View component. */
    private ViewBuilderInterface $viewBuilder;

    /** @var HeaderBuilderInterface A dedicated builder for the Header component. */
    private HeaderBuilderInterface $headerBuilder;

    /** @var FooterBuilderInterface A dedicated builder for the Footer component. */
    private FooterBuilderInterface $footerBuilder;

    /** @var NavigationBuilderInterface A dedicated builder for the Navigation component. */
    private NavigationBuilderInterface $navigationBuilder;

    /** @var PartialBuilderInterface A generic builder for creating partial views. */
    private PartialBuilderInterface $partialBuilder;

    /** @var AssetPathResolverInterface An asset path resolver to convert relative paths to web-accessible URLs. */
    private AssetPathResolverInterface $assetPathResolver;

    /**
     * @param PageBuilderInterface $pageBuilder The primary builder for the Page.
     * @param ViewBuilderInterface $viewBuilder The builder for the main View content.
     * @param HeaderBuilderInterface $headerBuilder The builder for the Header component.
     * @param FooterBuilderInterface $footerBuilder The builder for the Footer component.
     * @param NavigationBuilderInterface $navigationBuilder The builder for the Navigation component.
     * @param PartialBuilderInterface $partialBuilder A generic builder for nested partials.
     * @param AssetPathResolverInterface $assetPathResolver A service to resolve asset URLs.
     */
    public function __construct(
        PageBuilderInterface $pageBuilder,
        ViewBuilderInterface $viewBuilder,
        HeaderBuilderInterface $headerBuilder,
        FooterBuilderInterface $footerBuilder,
        NavigationBuilderInterface $navigationBuilder,
        PartialBuilderInterface $partialBuilder,
        AssetPathResolverInterface $assetPathResolver
    ) {
        $this->pageBuilder = $pageBuilder;
        $this->viewBuilder = $viewBuilder;
        $this->headerBuilder = $headerBuilder;
        $this->footerBuilder = $footerBuilder;
        $this->navigationBuilder = $navigationBuilder;
        $this->partialBuilder = $partialBuilder;
        $this->assetPathResolver = $assetPathResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title): self
    {
        $this->viewBuilder->setTitle($title);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setHeader(string $templateFile, array $data = [], array $partials = []): self
    {
        $this->headerBuilder->setTemplateFile($templateFile);
        $this->headerBuilder->setData($data);
        $this->headerBuilder->setPartials($partials);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFooter(string $templateFile, array $data = [], array $partials = []): self
    {
        $this->footerBuilder->setTemplateFile($templateFile);
        $this->footerBuilder->setData($data);
        $this->footerBuilder->setPartials($partials);
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
    public function setView(string $templateFile, array $data = [], array $partials = []): self
    {
        $this->viewBuilder->setTemplateFile($templateFile);
        $this->viewBuilder->setData($data);
        $this->viewBuilder->setPartials($partials);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNavigationLinks(array $links): self
    {
        $this->navigationBuilder->setLinks($links);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssets(array $assets): self
    {
        $resolvedAssets = [];
        foreach ($assets as $assetPath) {
            if (!is_string($assetPath)) {
                throw new InvalidArgumentException('Assets must be a flat array of strings.');
            }
            $resolvedAssets[] = $this->resolveAssetPath($assetPath);
        }
        $this->pageBuilder->setAssets($resolvedAssets);
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
    public function addViewPartial(string $name, string $templateFile, array $data = [], array $partials = []): self
    {
        $partial = $this->createPartialView($templateFile, $data, $partials);
        $this->viewBuilder->addPartial($name, $partial);
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
    public function buildPage(): PageInterface
    {
        $this->buildAndSetComponents();
        return $this->pageBuilder->build();
    }

    /**
     * Creates a generic partial view using the partial builder.
     *
     * @param string $templateFile The template file for the partial.
     * @param array $data The data for the partial.
     * @param array $partials Any nested partials.
     * @return PartialViewInterface The constructed partial view.
     */
    private function createPartialView(string $templateFile, array $data = [], array $partials = []): PartialViewInterface
    {
        return $this->partialBuilder
            ->setTemplateFile($templateFile)
            ->setData($data)
            ->setPartials($partials)
            ->build();
    }

    /**
     * Delegates asset path resolution to the injected resolver service.
     *
     * @param string $relativePath The asset's relative path.
     * @return string The resolved, web-accessible URL.
     */
    private function resolveAssetPath(string $relativePath): string
    {
        return $this->assetPathResolver->resolve($relativePath);
    }

    /**
     * Builds and assembles the primary page components before the final page build.
     * This method orchestrates the construction of the View, Navigation, Header, and Footer.
     */
    private function buildAndSetComponents(): void
    {
        $view = $this->viewBuilder->build();
        
        $navigation = $this->buildNavigation();
        $header = $this->buildHeader($navigation);
        $footer = $this->buildFooter();

        $this->pageBuilder->setView($view);
        $this->pageBuilder->setHeader($header);

        if ($footer !== null) {
            $this->pageBuilder->setFooter($footer);
        }
    }

    /**
     * Builds the navigation component if its builder is in a ready state.
     *
     * @return NavigationInterface|null The built navigation object, or null if not ready.
     */
    private function buildNavigation(): ?NavigationInterface
    {
        if (!$this->navigationBuilder->isReady()) {
            return null;
        }
        return $this->navigationBuilder->build();
    }

    /**
     * Builds the header component, injecting the navigation component if it exists.
     * The header is always built, as it is considered an essential part of the page layout.
     *
     * @param NavigationInterface|null $navigation The navigation component to inject.
     * @return PartialViewInterface The constructed header component.
     */
    private function buildHeader(?NavigationInterface $navigation = null): PartialViewInterface
    {
        if ($navigation !== null) {
            $this->headerBuilder->addPartial('navigation', $navigation);
        }
        return $this->headerBuilder->build();
    }

    /**
     * Builds the footer component if its builder is in a ready state.
     *
     * @return PartialViewInterface|null The built footer object, or null if not ready.
     */
    private function buildFooter(): ?PartialViewInterface
    {
        if (!$this->footerBuilder->isReady()) {
            return null;
        }
        return $this->footerBuilder->build();
    }
}
