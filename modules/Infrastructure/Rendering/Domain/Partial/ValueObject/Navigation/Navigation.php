<?php

declare(strict_types=1);

namespace Rendering\Domain\Partial\ValueObject\Navigation;

use Rendering\Domain\Contract\PartialViewInterface;

/**
 * An immutable Value Object representing a navigation menu component.
 *
 * It is composed of a list of NavigationLink objects and can contain its
 * own nested partials, providing a structured and type-safe way to render
 * a navigation bar.
 */
final class Navigation implements PartialViewInterface
{
    private const TEMPLATE = '/partial/navigation.phtml';

    /**
     * @var NavigationLink[] An array of navigation link objects.
     */
    private readonly array $links;

    /**
     * @param array<string, PartialViewInterface> $partials An associative array of nested partials.
     * @param NavigationLink ...$links A list of NavigationLink objects to include in the menu.
     */
    public function __construct(
        private readonly array $partials = [],
        NavigationLink ...$links,
    ) {
        $this->links = $links;
    }

    /**
     * {@inheritdoc}
     */
    public function fileName(): string
    {
        return self::TEMPLATE;
    }

    /**
     * {@inheritdoc}
     */
    public function data(): array
    {
        return [
            'links' => $this->links,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function partials(): array
    {
        return $this->partials;
    }
}