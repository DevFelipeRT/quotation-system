<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract;

/**
 * Defines the contract for a primary View object.
 *
 * A View represents the main, variable content for a specific application route
 * or action. It serves as the core content that is placed within a surrounding
 * page layout.
 *
 * This interface's primary purpose is to create a clear type distinction from
 * a `PartialViewInterface`, ensuring that methods designed to render main
 * content can only accept the correct type of view object.
 */
interface ViewInterface
{
    /**
     * Returns the unique identifier for the view's template file.
     *
     * This path is typically relative to a base views directory.
     *
     * @return string For example, 'pages/product-details.phtml'
     */
    public function fileName(): string;

    /**
     * Returns the data container for the view.
     *
     * This object encapsulates all variables that will be made available
     * to the template's scope during the rendering process.
     *
     * @return ViewDataInterface
     */
    public function data(): ViewDataInterface;
}