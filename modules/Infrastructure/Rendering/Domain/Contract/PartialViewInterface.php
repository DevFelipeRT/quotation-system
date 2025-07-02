<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract;

/**
 * Defines the contract for a reusable partial view component.
 *
 * A Partial View is a self-contained, renderable component that encapsulates
 * its own template, data, and, by extending PartialProviderInterface,
 * can also contain its own nested partial sub-components.
 */
interface PartialViewInterface extends PartialProviderInterface
{
    /**
     * Returns the template file identifier for the partial.
     *
     * @return string
     */
    public function fileName(): string;

    /**
     * Returns the data to be extracted into the partial's template scope.
     *
     * @return array<string, mixed>
     */
    public function data(): array;
}
