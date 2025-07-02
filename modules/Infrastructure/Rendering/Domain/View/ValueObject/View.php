<?php

declare(strict_types=1);

namespace Rendering\Domain\View\ValueObject;

use Rendering\Domain\Contract\ViewInterface;
use Rendering\Domain\Contract\ViewDataInterface;
use Rendering\Domain\Shared\ValueObject\ViewData;

/**
 * An immutable Value Object representing the main content of a page.
 *
 * It encapsulates a specific page's template file and its required data.
 * This class should be instantiated via the `create()` static factory method,
 * which provides a convenient and decoupled public API.
 */
final class View implements ViewInterface
{
    /**
     * @var string The path to the view's template file.
     */
    private readonly string $templateFile;

    /**
     * @var ViewDataInterface The data container for the view.
     */
    private readonly ViewDataInterface $dataProvider;

    /**
     * The constructor is private to enforce creation via the named constructor,
     * ensuring a consistent and simple public API.
     */
    private function __construct(string $templateFile, ViewDataInterface $dataProvider)
    {
        $this->templateFile = $templateFile;
        $this->dataProvider = $dataProvider;
    }

    /**
     * Creates a new View instance from a template file and a data array.
     * This static factory method is the intended public entry point for instantiation.
     *
     * @param string $templateFile The path to the view's template file.
     * @param array<string, mixed> $data The data to be made available to the template.
     * @return self
     */
    public static function create(string $templateFile, array $data = []): self
    {
        // Encapsulates the creation of the ViewData dependency.
        $viewData = new ViewData($data);

        return new self($templateFile, $viewData);
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
    public function data(): ViewDataInterface
    {
        return $this->dataProvider;
    }
}