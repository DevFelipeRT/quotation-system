<?php 

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building;

use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Domain\Contract\RenderableInterface;

/**
 * Interface for building renderable objects.
 *
 * This interface defines the methods required to construct a renderable object,
 * including setting the template file, data, and partials.
 */
interface RenderableBuilderInterface
{
    /**
     * Sets the template file for the renderable object.
     *
     * @param string $templateFile The path to the template file.
     * @return self
     */
    public function setTemplateFile(string $templateFile): self;

    /**
     * Sets the data for the renderable object.
     *
     * @param array $data An associative array of data to be used in the template.
     * @return self
     */
    public function setData(array $data): self;

    /**
     * Sets the partials for the renderable object.
     *
     * @param array $partials An associative array of PartialViewInterface objects.
     * @return self
     */
    public function setPartials(array $partials): self;

    /**
     * Adds a single partial to the renderable object.
     *
     * @param string $key The key under which the partial will be stored.
     * @param PartialViewInterface $partial The partial view object to be added.
     * @return self
     */
    public function addPartial(string $key, PartialViewInterface $partial): self;

    /**
     * Checks if the builder is ready to build the renderable object.
     *
     * This method should verify that all required properties (template file, data, etc.)
     * are set before calling the build method.
     *
     * @return bool True if the builder is ready, false otherwise.
     */
    public function isReady(): bool;

    /**
     * Builds and returns the final renderable object.
     *
     * @return RenderableInterface The constructed renderable object.
     */
    public function build(): RenderableInterface;
}