<?php 

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\View;

use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Infrastructure\Contract\Building\RenderableBuilderInterface;

/**
 * Interface for building a view object with a specific template, data, and partials.
 *
 * This interface extends the RenderableBuilderInterface to provide additional
 * methods specific to building views.
 */
interface ViewBuilderInterface extends RenderableBuilderInterface
{
    /**
     * Sets the title for the view.
     *
     * @param string $title The title of the view.
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * Builds the view object with the provided template file, data, and partials.
     *
     * @return ViewInterface The constructed view object.
     */
    public function build(): ViewInterface;
}