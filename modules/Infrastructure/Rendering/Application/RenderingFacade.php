<?php

declare(strict_types=1);

namespace Rendering\Application;

use Rendering\Domain\Contract\Page\PageInterface;
use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Domain\Contract\Service\BuildingServiceInterface;
use Rendering\Domain\Contract\Service\RenderingFacadeInterface;
use Rendering\Domain\Contract\Service\RenderingServiceInterface;

/**
 * The concrete implementation of the Rendering Facade.
 *
 * It orchestrates the page building and rendering services to provide a simple,
 * unified API for the application. This is the primary entry point for all
 * rendering tasks.
 */
final class RenderingFacade implements RenderingFacadeInterface
{
    /**
     * @param BuildingServiceInterface $buildingService The service responsible for assembling a Page object.
     * @param RenderingServiceInterface $renderingService The service responsible for rendering a Page object into HTML.
     */
    public function __construct(
        private readonly BuildingServiceInterface $buildingService,
        private readonly RenderingServiceInterface $renderingService
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle(string $title): self
    {
        $this->buildingService->setTitle($title);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeader(string $templateFile, array $data = [], array $partials = []): self
    {
        $this->buildingService->setHeader($templateFile, $data, $partials);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFooter(string $templateFile, array $data = [], array $partials = []): self
    {
        $this->buildingService->setFooter($templateFile, $data, $partials);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCopyright(string $owner, string $message = 'All rights reserved.'): self
    {
        $this->buildingService->setCopyright($owner, $message);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setView(string $templateFile, array $data = [], array $partials = []): self
    {
        $this->buildingService->setView($templateFile, $data, $partials);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setNavigationLinks(array $links): self
    {
        $this->buildingService->setNavigationLinks($links);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setAssets(array $assets): self
    {
        $this->buildingService->setAssets($assets);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addPagePartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self
    {
        $this->buildingService->addPagePartial($name, $templateFile, $data, $nestedPartials);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addHeaderPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self
    {
        $this->buildingService->addHeaderPartial($name, $templateFile, $data, $nestedPartials);
        return $this;
    }
    
    /**
     * {@inheritDoc}
     */
    public function addViewPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self
    {
        $this->buildingService->addViewPartial($name, $templateFile, $data, $nestedPartials);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addFooterPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self
    {
        $this->buildingService->addFooterPartial($name, $templateFile, $data, $nestedPartials);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function buildPage(): PageInterface
    {
        return $this->buildingService->buildPage();
    }

    /**
     * {@inheritDoc}
     */
    public function render(?RenderableInterface $page = null): string
    {
        $page = $page ?? $this->buildingService->buildPage();
        return $this->renderingService->render($page);
    }
}
