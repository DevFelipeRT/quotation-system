<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject\Page;

use InvalidArgumentException;
use Rendering\Domain\Contract\Page\AssetsInterface;
use Rendering\Domain\Trait\Validation\AssetsValidationTrait;

/**
 * Immutable value object representing a collection of page assets (CSS and JS).
 *
 * This class encapsulates lists of CSS and JavaScript file paths, ensuring
 * they are valid and providing an immutable structure for passing them to views.
 */
final class Assets implements AssetsInterface
{
    use AssetsValidationTrait;

    /**
     * @var string[] An array of CSS file paths.
     */
    private readonly array $css;

    /**
     * @var string[] An array of JavaScript file paths.
     */
    private readonly array $js;

    /**
     * Constructs a new Assets instance.
     *
     * @param string[] $css An array of CSS file paths.
     * @param string[] $js An array of JavaScript file paths.
     * @throws InvalidArgumentException if any file path is not a non-empty string.
     */
    public function __construct(array $css, array $js)
    {
        $this->validateAssetLinks($css, 'CSS');
        $this->validateAssetLinks($js, 'JavaScript');

        $this->css = array_values($css);
        $this->js = array_values($js);
    }

    /**
     * {@inheritdoc}
     */
    public function cssLinks(): array
    {
        return $this->css;
    }

    /**
     * {@inheritdoc}
     */
    public function jsLinks(): array
    {
        return $this->js;
    }

    /**
     * {@inheritdoc}
     */
    public function hasLink(string $path): bool
    {
        return in_array($path, $this->css, true) || in_array($path, $this->js, true);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return [
            'css' => $this->css,
            'js' => $this->js,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key): mixed
    {
        if ($key === 'css') {
            return $this->css;
        }

        if ($key === 'js') {
            return $this->js;
        }

        throw new InvalidArgumentException("Key '{$key}' does not exist. Valid keys are 'css' and 'js'.");
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return $key === 'css' || $key === 'js';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->all();
    }
}
