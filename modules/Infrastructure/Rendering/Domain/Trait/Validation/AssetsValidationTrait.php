<?php 

declare(strict_types=1);

namespace Rendering\Domain\Trait\Validation;

use InvalidArgumentException;
use Rendering\Domain\Contract\Page\AssetsInterface;

/**
 * Trait for validating assets in a collection.
 *
 * This trait provides methods to validate arrays of CSS and JavaScript links,
 * ensuring they are non-empty strings.
 */
trait AssetsValidationTrait
{
    /**
     * Validates the assets, ensuring they conform to the AssetsInterface.
     *
     * @param AssetsInterface $assets The assets to validate.
     * @throws InvalidArgumentException if the assets do not conform to the expected structure.
     */
    public function validateAssets(AssetsInterface $assets): void
    {
        $this->validateAssetLinks($assets->cssLinks(), 'CSS');
        $this->validateAssetLinks($assets->jsLinks(), 'JavaScript');
    }

    /**
     * Validates an array of asset links.
     *
     * @param string[] $links The array of links to validate.
     * @param string $type The type of asset (e.g., 'CSS', 'JavaScript').
     */
    public function validateAssetLinks(array $links, string $type): void
    {
        foreach ($links as $index => $link) {
            $this->validateAssetLink($link, $type, (string)$index);
        }
    }

    private function validateAssetLink(string $link, ?string $type = null, ?string $index = null): void
    {
        if (!is_string($link) || trim($link) === '') {
            $type = $type !== null ? " {$type}" : '';
            $position = $index !== null ? " at index {$index}" : '';
            throw new InvalidArgumentException(
                "The{$type} asset file path{$position} must be a non-empty string."
            );
        }
    }


}   