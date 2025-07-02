<?php

declare(strict_types=1);

namespace Rendering\Domain\Partial\ValueObject\Navigation;

/**
 * An immutable Value Object representing a single link within a navigation menu.
 *
 * It encapsulates all properties of a hyperlink, including its destination,
 * display text, and whether it represents the currently active page.
 */
final class NavigationLink
{
    /**
     * @param string $url      The link's destination URL (e.g., '/about').
     * @param string $label    The text to display for the link (e.g., 'About Us').
     * @param bool   $isActive Whether this link represents the current page.
     */
    public function __construct(
        private readonly string $url,
        private readonly string $label,
        private readonly bool   $isActive = false
    ) {
    }

    public function url(): string
    {
        return $this->url;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}