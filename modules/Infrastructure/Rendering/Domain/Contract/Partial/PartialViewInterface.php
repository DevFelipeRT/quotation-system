<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract\Partial;

use Rendering\Domain\Contract\RenderableInterface;

/**
 * Defines the contract for a reusable partial view component.
 *
 * A Partial View is a self-contained, renderable component that encapsulates
 * its own template, data, and, by extending PartialProviderInterface,
 * can also contain its own nested partial sub-components.
 */
interface PartialViewInterface extends RenderableInterface
{
}
