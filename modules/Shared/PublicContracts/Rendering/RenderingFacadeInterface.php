<?php

declare(strict_types=1);

namespace PublicContracts\Rendering;

/**
 * Defines the contract for the main Rendering Facade.
 *
 * This interface provides a single, simplified entry point for the entire
 * rendering module. It offers a fluent API to build and render a complete
 * page, hiding the underlying complexity of builders, factories, and renderers.
 */
interface RenderingFacadeInterface
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
     * Sets the main view content for the page.
     *
     * @param string $templateFile The view's template file identifier.
     * @param array<string, mixed> $data The data to be passed to the view template.
     * @return $this
     */
    public function setView(string $templateFile, array $data = []): self;

    /**
     * Builds and sets the navigation component from an array of link data.
     *
     * @param array<int, array<string, mixed>> $links An array of link definitions.
     * @return $this
     */
    public function setNavigationLinks(array $links): self;

    /**
     * Adds a named partial view to be injected into the main Page object.
     *
     * @param string $name The identifier for the partial (used with @partial).
     * @param string $templateFile The partial's template file identifier.
     * @param array<string, mixed> $data The data for the partial's template.
     * @param array $nestedPartials A nested array defining sub-partials for this partial.
     * @return $this
     */
    public function addPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self;

    /**
     * Adds a named partial view to be injected into the page header.
     *
     * @param string $name The identifier for the header partial.
     * @param string $templateFile The header partial's template file identifier.
     * @param array<string, mixed> $data The data for the header partial's template.
     * @param array $nestedPartials A nested array defining sub-partials for this partial.
     * @return $this
     */
    public function addHeaderPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self;

    /**
     * Adds a named partial view to be injected into the page footer.
     *
     * @param string $name The identifier for the footer partial.
     * @param string $templateFile The footer partial's template file identifier.
     * @param array<string, mixed> $data The data for the footer partial's template.
     * @param array $nestedPartials A nested array defining sub-partials for this partial.
     * @return $this
     */
    public function addFooterPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self;

    /**
     * Adds a named partial view to be injected into the page navigation.
     *
     * @param string $name The identifier for the navigation partial.
     * @param string $templateFile The navigation partial's template file identifier.
     * @param array<string, mixed> $data The data for the navigation partial's template.
     * @param array $nestedPartials A nested array defining sub-partials for this partial.
     * @return $this
     */
    public function addNavigationPartial(string $name, string $templateFile, array $data = [], array $nestedPartials = []): self;

    /**
     * Sets the page assets from a flat array of file paths.
     *
     * @param string[] $assets An array of asset file paths (CSS and JS).
     * @return $this
     */
    public function setAssets(array $assets): self;

    /**
     * Builds the configured page and renders it to an HTML string.
     * This is the final method in the fluent chain.
     *
     * @return string The fully rendered HTML document.
     */
    public function render(): string;
}
