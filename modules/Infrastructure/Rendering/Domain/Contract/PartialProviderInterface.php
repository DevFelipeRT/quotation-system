<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract;

/**
 * Defines the contract for any object that can provide partial view components.
 *
 * This interface enables a compositional architecture where both the main Page
 * and individual Partial Views can act as a context for rendering their own
 * nested sub-components.
 */
interface PartialProviderInterface
{
    /**
     * Returns an associative array of injectable partial view components.
     *
     * The key is the identifier used in a template via the @partial() directive,
     * and the value is the PartialViewInterface object to be rendered.
     *
     * @return array<string, \Rendering\Domain\Contract\PartialViewInterface>
     */
    public function partials(): array;
}
