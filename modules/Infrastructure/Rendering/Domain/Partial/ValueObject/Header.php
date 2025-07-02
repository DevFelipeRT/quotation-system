<?php

declare(strict_types=1);

namespace Rendering\Domain\Partial\ValueObject;

use InvalidArgumentException;
use Rendering\Domain\Contract\PartialViewInterface;

/**
 * An immutable Value Object representing the page's header component.
 *
 * It encapsulates the specific data needed for the header, such as the page
 * title and CSS links, and can also contain its own nested partial components,
 * ensuring all data is valid upon creation.
 */
final class Header implements PartialViewInterface
{
    /**
     * The template file associated with this component.
     */
    private const TEMPLATE = 'partial/header.phtml';

    /**
     * @var string The main title of the page.
     */
    private readonly string $title;

    /**
     * @var string[] An array of CSS file paths to include.
     */
    private readonly array $cssLinks;

    /**
     * @var array<string, PartialViewInterface> An associative array of nested partials for this header.
     */
    private readonly array $partials;

    /**
     * @param string $title The main title of the page.
     * @param string[] $cssLinks An array of CSS file paths to include.
     * @param array<string, PartialViewInterface> $partials An associative array of nested partials for this header.
     */
    public function __construct(
        string $title,
        array $cssLinks = [],
        array $partials = []
    ) {
        $this->title = $title;

        // Validate the incoming data before assigning it.
        $this->validateCssLinks($cssLinks);
        $this->validatePartials($partials);

        // Assign the validated data to the properties.
        $this->cssLinks = $cssLinks;
        $this->partials = $partials;
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
            'title' => $this->title,
            'cssLinks' => $this->cssLinks,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function partials(): array
    {
        return $this->partials;
    }

    /**
     * Validates that the provided CSS links are valid strings.
     */
    private function validateCssLinks(array $cssFiles): void
    {
        foreach ($cssFiles as $index => $file) {
            if (!is_string($file) || trim($file) === '') {
                throw new InvalidArgumentException("CSS asset at index {$index} must be a non-empty string.");
            }
        }
    }

    /**
     * Validates that the provided partials array is correctly structured.
     */
    private function validatePartials(array $partials): void
    {
        foreach ($partials as $identifier => $partial) {
            if (!is_string($identifier) || trim($identifier) === '') {
                throw new InvalidArgumentException("Partial identifier must be a non-empty string.");
            }
            if (!($partial instanceof PartialViewInterface)) {
                $type = is_object($partial) ? get_class($partial) : gettype($partial);
                throw new InvalidArgumentException(
                    "Partial with identifier '{$identifier}' must be an instance of PartialViewInterface, but {$type} was given."
                );
            }
        }
    }
}
