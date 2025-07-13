<?php

declare(strict_types=1);

namespace Rendering\Domain\Trait\Validation;

use InvalidArgumentException;
use Rendering\Domain\Contract\Partial\Navigation\NavigationLinkInterface;

trait NavigationLinkValidationTrait
{
    /**
     * Validates an array of links, ensuring each item is a valid NavigationLinkInterface.
     *
     * @param array $links
     * @throws InvalidArgumentException if any item in the array is not a valid NavigationLinkInterface.
     */
    public function validateLinkArray(array $links): void
    {
        foreach ($links as $index => $link) {
            $this->validateNavigationLink($link, (string)$index);
        }
    }

    /**
     * Validates a NavigationLinkInterface instance.
     *
     * @param mixed $link
     * @param string|null $index Optional index for error messages.
     * @throws InvalidArgumentException if $link is not a valid NavigationLinkInterface.
     */
    public function validateNavigationLink(mixed $link, ?string $index = null): void
    {
        if (!($link instanceof NavigationLinkInterface)) {
            $type = is_object($link) ? get_class($link) : gettype($link);
            $position = $index !== null ? " at index {$index}" : '';
            
            throw new InvalidArgumentException(
                "The link{$position} must be an instance of NavigationLinkInterface, but {$type} was given."
            );
        }
    }
}
