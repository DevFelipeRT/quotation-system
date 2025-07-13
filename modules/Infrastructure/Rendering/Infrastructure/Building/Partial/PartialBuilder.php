<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Partial;

use Rendering\Infrastructure\Building\AbstractRenderableBuilder;
use Rendering\Infrastructure\Contract\Building\Partial\PartialBuilderInterface;
use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Domain\ValueObject\Partial\PartialView;

class PartialBuilder extends AbstractRenderableBuilder implements PartialBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(): PartialViewInterface
    {
        return new PartialView(
            $this->templateFile, 
            $this->buildDataFromArray($this->data), 
            $this->buildPartialsCollection($this->partials)
        );
    }
}