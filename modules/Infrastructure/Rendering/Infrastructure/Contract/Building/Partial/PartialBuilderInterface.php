<?php 

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Partial;

use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Infrastructure\Contract\Building\RenderableBuilderInterface;

/**
 * Defines the contract for a Partial component builder.
 * 
 * This interface extends the RenderableBuilderInterface and
 * provides a fluent API to encapsulate the construction
 * logic of a Partial object, which is a reusable
 * component that can be rendered independently within a page.
 */
interface PartialBuilderInterface extends RenderableBuilderInterface
{
    /**
     * Builds and returns a partial view instance.
     *
     * @return PartialViewInterface The constructed partial view.
     */
    public function build(): PartialViewInterface;
}