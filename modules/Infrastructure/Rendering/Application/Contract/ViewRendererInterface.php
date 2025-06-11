<?php

declare(strict_types=1);

namespace Rendering\Application\Contract;

use Rendering\Domain\Contract\ViewInterface;

/**
 * ViewRendererInterface
 *
 * Defines the contract for rendering a view object to a string-based output
 * (such as HTML, JSON, XML) in the Presentation layer.
 *
 * Implementations of this interface serve as output adapters, responsible for
 * transforming immutable view value objects into complete responses suitable
 * for delivery to the client.
 *
 * The ViewInterface object must encapsulate both the template identifier and
 * all associated rendering data.
 *
 * @package Rendering/Application/Contract
 */
interface ViewRendererInterface
{
    /**
     * Renders the provided view into a complete output string.
     *
     * @param ViewInterface $view Immutable view object containing all data and template information.
     * @return string Rendered content, ready to be sent in an HTTP response.
     */
    public function render(ViewInterface $view): string;
}
