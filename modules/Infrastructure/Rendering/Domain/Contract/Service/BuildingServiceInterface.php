<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract\Service;

use Rendering\Domain\Contract\Page\PageInterface;

/**
 * Defines the contract for the high-level Page Building Service.
 *
 * This interface provides a simplified public API for clients (like controllers)
 * to construct a complete Page object. It acts as a Facade, hiding the
 * complexities of the underlying builder and factory components.
 */
interface BuildingServiceInterface
{
    /**
     * Sets the main title for the page.
     *
     * @param string $title The title to be used in the page header.
     * @return $this
     */
    public function setTitle(string $title): self;

    /**
     * Sets the copyright information for the page footer.
     *
     * @param string $owner The name of the copyright owner.
     * @param string $message Optional. The copyright message to display.
     * @return $this
     */
    public function setCopyright(string $owner, string $message = 'All rights reserved.'): self;

    /**
     * Sets the main header component for the page.
     *
     * @param string $templateFile The template file for the header.
     * @param array  $data         Optional data for the header template.
     * @param array  $partials     Optional nested partials within the header.
     * @return self
     */
    public function setHeader(string $templateFile, array $data = [], array $partials = []): self;
    
    /**
     * Sets the main footer component for the page.
     *
     * @param string $templateFile The template file for the footer.
     * @param array  $data         Optional data for the footer template.
     * @param array  $partials     Optional nested partials within the footer.
     * @return self
     */
    public function setFooter(string $templateFile, array $data = [], array $partials = []): self;
    
    /**
     * Sets the main view content for the page.
     *
     * @param string $templateFile The view's template file identifier.
     * @param array<string, mixed> $data The data to be passed to the view template.
     * @param array $partials A nested array defining sub-partials for this view.
     * @return $this
     */
    public function setView(string $templateFile, array $data = [], array $partials = []): self;

    /**
     * Builds and sets the navigation component from an array of link data.
     *
     * @param array<int, array<string, mixed>> $links An array of link definitions.
     * @return $this
     */
    public function setNavigationLinks(array $links): self;

    /**
     * Sets the page assets from a flat array of file paths.
     *
     * @param string[] $assets An array of asset file paths (CSS and JS).
     * @return $this
     */
    public function setAssets(array $assets): self;

    /**
     * Adds a named partial view to be injected into the main Page object.
     *
     * @param string $name The identifier for the partial (used with @partial).
     * @param string $templateFile The partial's template file identifier.
     * @param array<string, mixed> $data The data for the partial's template.
     * @param array $partials A nested array defining sub-partials for this partial.
     * @return $this
     */
    public function addPagePartial(string $name, string $templateFile, array $data = [], array $partials = []): self;

    /**
     * Adds a named view partial to be injected into the main view.
     *
     * @param string $name The identifier for the view partial.
     * @param string $templateFile The view partial's template file identifier.
     * @param array<string, mixed> $data The data for the view partial's template.
     * @param array $partials A nested array defining sub-partials for this partial.
     * @return $this
     */
    public function addViewPartial(string $name, string $templateFile, array $data = [], array $partials = []): self;

    /**
     * Adds a named partial view to be injected into the page header.
     *
     * @param string $name The identifier for the header partial.
     * @param string $templateFile The header partial's template file identifier.
     * @param array<string, mixed> $data The data for the header partial's template.
     * @param array $partials A nested array defining sub-partials for this partial.
     * @return $this
     */
    public function addHeaderPartial(string $name, string $templateFile, array $data = [], array $partials = []): self;

    /**
     * Adds a named partial view to be injected into the page footer.
     *
     * @param string $name The identifier for the footer partial.
     * @param string $templateFile The footer partial's template file identifier.
     * @param array<string, mixed> $data The data for the footer partial's template.
     * @param array $partials A nested array defining sub-partials for this partial.
     * @return $this
     */
    public function addFooterPartial(string $name, string $templateFile, array $data = [], array $partials = []): self;

    /**
     * Builds the final, immutable Page object based on the provided configuration.
     *
     * @return PageInterface The fully assembled Page object, ready for rendering.
     */
    public function buildPage(): PageInterface;
}
