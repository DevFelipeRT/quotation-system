<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Partial;

use Rendering\Domain\Contract\Partial\Navigation\NavigationInterface;

/**
 * Defines the contract for a Navigation component builder.
 * 
 * This interface extends the PartialBuilderInterface to provide
 * a fluent API for constructing a Navigation object,
 * including its links and any nested partial sub-components.
 */
interface NavigationBuilderInterface extends PartialBuilderInterface
{
    /**
     * Adds a single link to the navigation.
     *
     * @param string $label    The text to display for the link.
     * @param string $url      The URL the link points to.
     * @param bool   $isActive Whether the link is currently active (e.g., for highlighting).
     * @return self
     */
    public function addLink(string $label, string $url, bool $isActive = false): self;

    /**
     * Adds multiple links to the navigation.
     *
     * @param array $links An array of associative arrays with 'label', 'url' and 'isActive' keys.
     * @return self
     */
    public function setLinks(array $links): self;

    /**
     * Assembles and returns the final, immutable Navigation object.
     *
     * @return NavigationInterface
     */
    public function build(): NavigationInterface;
}
