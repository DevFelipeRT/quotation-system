<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Partials;

use Rendering\Infrastructure\Contract\Building\Partial\HeaderBuilderInterface;
use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Partial\ValueObject\Header;

/**
 * Implements the Builder pattern to assemble a complete Header object.
 */
final class HeaderBuilder implements HeaderBuilderInterface
{
    private string $title = '';
    private array $cssLinks = [];
    private array $partials = [];

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCss(string $path): self
    {
        if (!in_array($path, $this->cssLinks, true)) {
            $this->cssLinks[] = $path;
        }
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
    public function build(): Header
    {
        return new Header(
            $this->title,
            $this->cssLinks,
            $this->partials
        );
    }
}
