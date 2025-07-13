<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract;

/**
 * Defines the contract for any object that can be rendered.
 *
 * An object implementing this interface guarantees that it can provide
 * a template file name and a corresponding data object (RenderableDataInterface)
 * required for its rendering.
 */
interface RenderableInterface extends PartialProviderInterface
{
    /**
     * Gets the template file name associated with the object.
     *
     * @return string The path or name of the template file.
     */
    public function fileName(): string;

    /**
     * Gets the data object to be passed to the template.
     *
     * @return RenderableDataInterface The data container for the view.
     */
    public function data(): ?RenderableDataInterface;
}
