<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Partial;

use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Domain\ValueObject\Partial\Header;
use Rendering\Infrastructure\Building\Factory\PartialFactory;
use Rendering\Infrastructure\Building\Factory\RenderableDataFactory;
use Rendering\Infrastructure\Contract\Building\Partial\HeaderBuilderInterface;

/**
 * Implements the Builder pattern to assemble a complete Header object.
 */
final class HeaderBuilder extends PartialBuilder implements HeaderBuilderInterface
{
    private const DEFAULT_TEMPLATE = 'partial/header.phtml';

    /**
     * Constructor to initialize the HeaderBuilder with necessary factories.
     * Sets the default template file for the header.
     * Template files can be overridden by calling setTemplateFile().
     *
     * @param PartialFactory $partialFactory Factory to create partial views.
     * @param RenderableDataFactory $dataFactory Factory to create renderable data.
     */
    public function __construct(
        PartialFactory $partialFactory,
        RenderableDataFactory $dataFactory
    ) {
        parent::__construct($partialFactory, $dataFactory);
        $this->initializeTemplateFile(self::DEFAULT_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function build(): PartialViewInterface
    {
        return new Header(
            $this->templateFile,
            $this->buildDataFromArray($this->data),
            $this->buildPartialsCollection($this->partials)
        );
    }
}
