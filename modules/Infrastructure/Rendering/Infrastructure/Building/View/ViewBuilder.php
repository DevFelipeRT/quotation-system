<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\View;

use Rendering\Infrastructure\Contract\Building\View\ViewBuilderInterface;
use Rendering\Infrastructure\Building\AbstractRenderableBuilder;
use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Domain\ValueObject\View\View;

/**
 * ViewBuilder is responsible for constructing a View object with a specific template,
 * data, and partials. It extends the AbstractRenderableBuilder to leverage common
 * functionality for rendering.
 */
class ViewBuilder extends AbstractRenderableBuilder implements ViewBuilderInterface
{
    private string $title = '';

    /**
     * Sets the title for the view.
     *
     * @param string $title The title of the view.
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function build(): ViewInterface
    {
        return new View(
            $this->templateFile, 
            $this->buildDataFromArray($this->data), 
            $this->buildPartialsCollection($this->partials),
            $this->title ?? null
        );
    }
}
