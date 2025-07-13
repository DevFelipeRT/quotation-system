<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject\Partial\Navigation;

use Rendering\Domain\ValueObject\Partial\PartialView;
use Rendering\Domain\Contract\Partial\Navigation\NavigationInterface;
use Rendering\Domain\Contract\RenderableDataInterface;
use Rendering\Domain\ValueObject\Shared\PartialsCollection;

/**
 * An immutable Value Object representing a navigation menu component.
 *
 * It is composed of a list of NavigationLink objects and can contain its
 * own nested partials, providing a structured and type-safe way to render
 * a navigation bar.
 */
final class Navigation extends PartialView implements NavigationInterface
{
    private readonly NavigationLinkCollection $links;

    public function __construct(
        string $templateFile, 
        NavigationLinkCollection $links,
        ?RenderableDataInterface $dataProvider,
        ?PartialsCollection $partials,
    ) {
        $this->links = $links;
        parent::__construct(
            $templateFile,
            $dataProvider,
            $partials
        );
    }

    /**
     * Returns the collection of navigation links.
     *
     * @return NavigationLink[]
     */
    public function links(): NavigationLinkCollection
    {
        return $this->links;
    }
}