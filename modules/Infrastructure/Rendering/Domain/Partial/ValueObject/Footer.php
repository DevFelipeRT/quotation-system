<?php

declare(strict_types=1);

namespace Rendering\Domain\Partial\ValueObject;

use InvalidArgumentException;
use Rendering\Domain\Contract\PartialViewInterface;

/**
 * An immutable Value Object representing the page's footer component.
 *
 * It encapsulates the specific data needed for the footer, such as the
 * copyright notice and JavaScript links, and can also contain its own
 * nested partial components, ensuring all data is valid upon creation.
 */
final class Footer implements PartialViewInterface
{
    /**
     * The template file associated with this component.
     */
    private const TEMPLATE = 'partial/footer.phtml';

    /**
     * @var string The copyright text to be displayed.
     */
    private readonly string $copyrightNotice;

    /**
     * @var string[] An array of JavaScript file paths to include.
     */
    private readonly array $jsLinks;

    /**
     * @var array<string, PartialViewInterface> An associative array of nested partials for this footer.
     */
    private readonly array $partials;

    /**
     * @param string $copyrightNotice The copyright text to be displayed.
     * @param string[] $jsLinks An array of JavaScript file paths to include.
     * @param array<string, PartialViewInterface> $partials An associative array of nested partials for this footer.
     */
    public function __construct(
        string $copyrightNotice,
        array $jsLinks = [],
        array $partials = []
    ) {
        $this->copyrightNotice = $copyrightNotice;

        // Validate the incoming data before assigning it.
        $this->validateJsLinks($jsLinks);
        $this->validatePartials($partials);

        // Assign the validated data to the properties.
        $this->jsLinks = $jsLinks;
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
            'copyrightNotice' => $this->copyrightNotice,
            'jsLinks' => $this->jsLinks,
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
     * Validates that the provided JavaScript links are valid strings.
     */
    private function validateJsLinks(array $jsFiles): void
    {
        foreach ($jsFiles as $index => $file) {
            if (!is_string($file) || trim($file) === '') {
                throw new InvalidArgumentException("JavaScript asset at index {$index} must be a non-empty string.");
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
