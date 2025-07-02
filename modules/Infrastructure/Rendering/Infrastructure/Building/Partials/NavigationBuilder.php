<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Partials;

use Rendering\Infrastructure\Contract\Building\Partial\NavigationBuilderInterface;
use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Partial\ValueObject\Navigation\Navigation;
use Rendering\Domain\Partial\ValueObject\Navigation\NavigationLink;

/**
 * Implements the Builder pattern to assemble a complete Navigation object.
 */
final class NavigationBuilder implements NavigationBuilderInterface
{
    /**
     * @var NavigationLink[]
     */
    private array $links = [];

    /**
     * @var array<string, PartialViewInterface>
     */
    private array $partials = [];

    /**
     * {@inheritdoc}
     */
    public function addLink(NavigationLink $link): self
    {
        $this->links[] = $link;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPartial(string $key, PartialViewInterface $partial): self
    {
        $this->partials[$key] = $partial;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function build(): Navigation
    {
        return new Navigation(
            $this->partials,
            ...$this->links
        );
    }
}
