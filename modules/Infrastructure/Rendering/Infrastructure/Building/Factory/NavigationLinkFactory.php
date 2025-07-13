<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Factory;

use InvalidArgumentException;
use Rendering\Domain\Contract\Partial\Navigation\NavigationLinkInterface;
use Rendering\Domain\ValueObject\Partial\Navigation\NavigationLink;
use Rendering\Domain\ValueObject\Partial\Navigation\NavigationLinkCollection;

final class NavigationLinkFactory
{
    /**
     * Creates a new NavigationLink object with the given label, URL, and optional attributes.
     *
     * @param string $label    The text to display for the link.
     * @param string $url      The URL the link points to.
     * @param bool   $isActive Whether the link is currently active (e.g., for highlighting).
     * @return NavigationLinkInterface The constructed NavigationLink object.
     */
    public function createNavigationLink(string $label, string $url, bool $isActive = false): NavigationLinkInterface
    {
        if (empty($label) || empty($url)) {
            throw new InvalidArgumentException('Label and URL cannot be empty.');
        }

        return new NavigationLink($url, $label, $isActive);
    }

    public function createNavigationLinkFromArray(array $linkData): NavigationLinkInterface
    {
        if (!isset($linkData['label'], $linkData['url'])) {
            throw new InvalidArgumentException('Each link must have "label" and "url" keys.');
        }

        return $this->createNavigationLink(
            $linkData['label'],
            $linkData['url'],
            $linkData['active'] ?? false
        );
    }

    public function createNavigationLinkCollection(array $links): NavigationLinkCollection
    {
        $navigationLinks = [];
        foreach ($links as $link) {
            if ($link instanceof NavigationLinkInterface) {
                $navigationLinks[] = $link;
                continue;
            }
            if (!is_array($link) && !isset($link['label'], $link['url'])) {
                throw new InvalidArgumentException('Each link must be an instance of NavigationLink or an associative array with "label" and "url" keys.');
            }
            $navigationLinks[] = $this->createNavigationLinkFromArray($link);
        }
        return new NavigationLinkCollection($navigationLinks);
    }
}