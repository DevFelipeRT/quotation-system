<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Factory;

use InvalidArgumentException;
use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Domain\ValueObject\Partial\PartialView;
use Rendering\Domain\ValueObject\Shared\PartialsCollection;

final class PartialFactory
{
    private RenderableDataFactory $factory;

    /**
     * Constructor for the PartialFactory.
     *
     * @param RenderableDataFactory $factory A factory to create renderable data objects from arrays.
     */
    public function __construct(RenderableDataFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Creates a new PartialView object with the given template, data, and nested partials.
     *
     * @param string $template The template file path for the partial.
     * @param array  $data     An associative array of data to be passed to the partial.
     * @param array  $partials An associative array of nested partials,where keys are identifiers and values are either PartialViewInterface objects or arrays defining nested partials.
     * @return PartialViewInterface The constructed PartialView object.
     */
    public function createPartial(string $template, array $data = [], array $partials = []): PartialViewInterface
    {
        return new PartialView(
            $template,
            $this->factory->createRenderableDataFromArray($data),
            $this->hydratePartials($partials)
        );
    }

    /**
     * Creates a collection of PartialView objects from an array of partial definitions.
     *
     * @param array $partials An associative array where keys are identifiers and values are either PartialViewInterface objects or arrays defining nested partials.
     * @return PartialsCollection|null A collection of hydrated PartialView objects or null if the input is empty.
     */
    public function createPartialsCollection(array $partials): ?PartialsCollection
    {
        if (empty($partials)) {
            return null;
        }

        return $this->hydratePartials($partials);
    }

    /**
     * Iterates over a raw partials array and hydrates each definition.
     *
     * @param array $rawPartials The array of partial definitions.
     * @return PartialsCollection|null A collection of hydrated PartialView objects or null if the input is empty.
     */
    protected function hydratePartials(array $rawPartials): ?PartialsCollection
    {
        $hydrated = [];
        foreach ($rawPartials as $key => $partialData) {
            if ($partialData instanceof PartialViewInterface) {
                $hydrated[$key] = $partialData;
                continue;
            }

            if (is_array($partialData)) {
                $hydrated[$key] = $this->createPartialFromArray($key, $partialData);
                continue;
            }
            
            throw new InvalidArgumentException("Invalid partial definition for key '{$key}'. Must be an array or a PartialViewInterface object.");
        }
        if (empty($hydrated)) {
            return null;
        }
        return new PartialsCollection($hydrated);
    }

    /**
     * Builds a single PartialView object from its array definition.
     *
     * @param string|int $key The identifier key for error reporting.
     * @param array $definition The array containing [template, data, partials].
     * @return PartialView The constructed PartialView object.
     */
    private function createPartialFromArray($key, array $definition): PartialViewInterface
    {
        $nestedTemplate = $definition[0] ?? null;
        $nestedData = $definition[1] ?? [];
        $nestedPartials = $definition[2] ?? [];

        if (!is_string($nestedTemplate)) {
            throw new InvalidArgumentException("Invalid partial definition for key '{$key}'. Template file (index 0) must be a string.");
        }

        // Recursively call the main public method to build the nested partial.
        return $this->createPartial(
            $nestedTemplate,
            $nestedData,
            $nestedPartials
        );
    }
}