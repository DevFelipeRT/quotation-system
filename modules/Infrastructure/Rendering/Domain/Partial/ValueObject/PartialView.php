<?php

declare(strict_types=1);

namespace Rendering\Domain\Partial\ValueObject;

use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Contract\ViewDataInterface;
use Rendering\Domain\Shared\ValueObject\ViewData;

/**
 * A generic, immutable Value Object for rendering any reusable template fragment.
 *
 * This class is used for partials that do not have a dedicated, specific VO.
 * It encapsulates its template, data, and can also contain its own nested
 * partial sub-components. It should be instantiated via the `create()` static
 * factory method.
 */
final class PartialView implements PartialViewInterface
{
    /**
     * @var string The path to the partial's template file.
     */
    private readonly string $templateFile;

    /**
     * @var ViewDataInterface The data container for the partial.
     */
    private readonly ViewDataInterface $dataProvider;

     /**
     * An associative array of nested partial components.
     * @var array<string, PartialViewInterface>
     */
    private readonly array $partials;

    /**
     * The constructor is private to enforce creation via the named constructor.
     */
    private function __construct(
        string $templateFile, 
        ViewDataInterface $dataProvider,
        array $partials
    ){
        $this->templateFile = $templateFile;
        $this->dataProvider = $dataProvider;
        $this->partials = $partials;
    }

    /**
     * Creates a new PartialView instance.
     *
     * @param string $templateFile The path to the partial's template file.
     * @param array<string, mixed> $data The data for the template.
     * @param array<string, PartialViewInterface> $partials Nested partials for this component.
     * @return self
     */
    public static function create(
        string $templateFile, 
        array $data = [],
        array $partials = []
    ): self {
        $viewData = new ViewData($data);
        return new self($templateFile, $viewData, $partials);
    }

    /**
     * {@inheritdoc}
     */
    public function fileName(): string
    {
        return $this->templateFile;
    }

    /**
     * {@inheritdoc}
     */
    public function data(): array
    {
        return $this->dataProvider->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function partials(): array
    {
        return $this->partials;
    }
}