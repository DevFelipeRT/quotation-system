<?php 

declare(strict_types=1);

namespace Rendering\Domain\Contract\Partial\Navigation;

/**
 * Defines the contract for a single navigation link within a navigation menu.
 *
 * This interface specifies the methods required to retrieve the URL, label,
 * and active state of a navigation link, allowing for consistent rendering
 * and interaction within navigation components.
 */
interface NavigationLinkInterface
{
    /**
     * Returns the URL for the navigation link.
     *
     * @return string The URL to navigate to when the link is clicked.
     */
    public function url(): string;

    /**
     * Returns the label for the navigation link.
     *
     * @return string The text displayed for the link.
     */
    public function label(): string;

    /**
     * Checks if this link is the currently active page.
     *
     * @return bool True if this link represents the current page, false otherwise.
     */
    public function isActive(): bool;
}