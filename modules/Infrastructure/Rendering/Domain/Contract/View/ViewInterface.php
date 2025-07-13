<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract\View;

use Rendering\Domain\Contract\RenderableInterface;

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
interface ViewInterface extends RenderableInterface
{
    /**
     * Returns the title of the view, which can be used for SEO or page headers.
     *
     * @return string
     */
    public function title(): string;
}