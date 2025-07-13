<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject\Partial;

use Rendering\Domain\ValueObject\Shared\Renderable;
use Rendering\Domain\Contract\Partial\PartialViewInterface;

/**
 * A generic, immutable Value Object for rendering any reusable template fragment.
 *
 * This class is used for partials that do not have a dedicated, specific VO.
 * It encapsulates its template, data, and can also contain its own nested
 * partial sub-components.
 */
class PartialView extends Renderable implements PartialViewInterface
{
}