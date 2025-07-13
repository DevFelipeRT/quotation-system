<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component\Renderer;

/**
 * A specialized renderer for objects that implement PartialViewInterface.
 *
 * This class leverages the common rendering logic from AbstractComponentRenderer
 * and only provides the logic to identify that it supports PartialView objects.
 */
final class PartialRenderer extends AbstractComponentRenderer
{
}
