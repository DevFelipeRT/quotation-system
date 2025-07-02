<?php

declare(strict_types=1);

namespace Rendering\Application;

use PublicContracts\Rendering\RenderingFacadeInterface;
use Rendering\Application\Contract\PageBuildingServiceInterface;
use Rendering\Application\Contract\PageRenderingServiceInterface;

/**
 * The concrete implementation of the Rendering Facade.
 *
 * It orchestrates the page building and rendering services to provide a simple,
 * unified API for the application.
 */
final class RenderingFacade implements RenderingFacadeInterface
{
    /**
     * @param PageBuildingServiceInterface $pageBuildingService The service responsible for assembling a Page object.
     * @param PageRenderingServiceInterface $pageRenderingService The service responsible for rendering a Page object into HTML.
     */
    public function __construct(
        private readonly PageBuildingServiceInterface $pageBuildingService,
        private readonly PageRenderingServiceInterface $pageRenderingService
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle(string $title): self
    {
        $this->pageBuildingService->setTitle($title);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCopyright(string $owner, string $message = 'All rights reserved.'): self
    {
        $this->pageBuildingService->setCopyright($owner, $message);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setView(string $templateFile, array $data = []): self
    {
        $this->pageBuildingService->setView($templateFile, $data);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setNavigationLinks(array $links): self
    {
        $this->pageBuildingService->setNavigationLinks($links);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self
    {
        $this->pageBuildingService->addPagePartial($name, $templateFile, $data, $nestedPartials);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addHeaderPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self
    {
        $this->pageBuildingService->addHeaderPartial($name, $templateFile, $data, $nestedPartials);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addFooterPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self
    {
        $this->pageBuildingService->addFooterPartial($name, $templateFile, $data, $nestedPartials);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addNavigationPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self
    {
        $this->pageBuildingService->addNavigationPartial($name, $templateFile, $data, $nestedPartials);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setAssets(array $assets): self
    {
        $this->pageBuildingService->setAssets($assets);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function render(): string
    {
        $page = $this->pageBuildingService->buildPage();
        return $this->pageRenderingService->renderPage($page);
    }
}
