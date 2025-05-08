<?php

namespace App\Infrastructure\Rendering\Infrastructure;

/**
 * ViewRendererInterface
 *
 * Defines the contract for classes responsible for rendering views
 * into string-based output (e.g., HTML, JSON, XML), based on the ViewInterface.
 *
 * Implementations of this interface serve as output adapters in the Presentation layer.
 */
interface ViewRendererInterface
{
    /**
     * Renders the provided view into a complete output string.
     *
     * @param ViewInterface $view A view object containing a template identifier and its associated data.
     * @return string The rendered content ready to be returned in an HTTP response.
     */
    public function render(ViewInterface $view): string;
}
