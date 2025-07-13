<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject\View;

use Rendering\Domain\ValueObject\Shared\Renderable;
use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Domain\Contract\RenderableDataInterface;
use Rendering\Domain\ValueObject\Shared\PartialsCollection;

/**
 * An immutable Value Object representing the main content of a page.
 *
 * It encapsulates a specific page's template file and its required data.
 */
final class View extends Renderable implements ViewInterface
{
    /**
     * @var string The title of the view, which can be used for SEO or page headers.
     */
    private readonly string $title;


    /**
     * Constructs a new View instance.
     *
     * @param string $templateFile The path to the template file for this view.
     * @param RenderableDataInterface|null $dataProvider Optional data provider for dynamic content.
     * @param PartialsCollection|null $partials Optional collection of partials to include in the view.
     * @param string|null $title Optional title for the view, defaults to an empty string if not provided.
     */
    public function __construct(
        string $templateFile, 
        ?RenderableDataInterface $dataProvider,
        ?PartialsCollection $partials = null,
        ?string $title = null
    ) {
        $this->title = $title ?? '';
        parent::__construct($templateFile, $dataProvider, $partials);
    }

    /**
     * {@inheritdoc}
     */
    public function title(): string
    {
        return $this->title;
    }
}