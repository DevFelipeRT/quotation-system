<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Partial;

use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Partial\ValueObject\Navigation\Navigation;
use Rendering\Domain\Partial\ValueObject\Navigation\NavigationLink;

/**
 * Defines the contract for a Navigation component builder.
 *
 * This interface provides a fluent API to encapsulate the construction
 * logic of a Navigation object, including its links and any nested
 * partial sub-components.
 */
interface NavigationBuilderInterface
{
    /**
     * Adds a single NavigationLink object to the menu.
     *
     * @param NavigationLink $link The link object to add.
     * @return $this
     */
    public function addLink(NavigationLink $link): self;

    /**
     * Adds a named partial sub-component to the navigation.
     *
     * @param string $key The identifier for the partial (used with @partial).
     * @param PartialViewInterface $partial The partial view object to add.
     * @return $this
     */
    public function addPartial(string $key, PartialViewInterface $partial): self;

    /**
     * Assembles and returns the final, immutable Navigation object.
     *
     * @return Navigation
     */
    public function build(): Navigation;
}
