<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Partials;

use InvalidArgumentException;
use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Partial\ValueObject\PartialView;
use Rendering\Infrastructure\Contract\Building\Partial\PartialFactoryInterface;

/**
 * Implements the Factory pattern to recursively create PartialView objects.
 *
 * This factory can hydrate a multi-dimensional array definition into a
 * nested tree of PartialView objects, allowing for the construction
 * of complex, composite components from a single data structure.
 */
final class PartialFactory implements PartialFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createPartialView(
        string $templateFile,
        array $data = [],
        array $partials = []
    ): PartialView {
        $hydratedPartials = $this->hydratePartials($partials);

        return PartialView::create($templateFile, $data, $hydratedPartials);
    }

    /**
     * Iterates over a raw partials array and hydrates each definition.
     *
     * @param array $rawPartials The array of partial definitions.
     * @return array<string, PartialViewInterface> The hydrated partial objects.
     */
    private function hydratePartials(array $rawPartials): array
    {
        $hydrated = [];
        foreach ($rawPartials as $key => $partialData) {
            // If the item is already a PartialView object, use it directly.
            if ($partialData instanceof PartialViewInterface) {
                $hydrated[$key] = $partialData;
                continue;
            }

            // If the item is an array, delegate its construction.
            if (is_array($partialData)) {
                $hydrated[$key] = $this->buildPartialFromArray($key, $partialData);
                continue;
            }
            
            throw new InvalidArgumentException("Invalid partial definition for key '{$key}'. Must be an array or a PartialViewInterface object.");
        }
        return $hydrated;
    }

    /**
     * Builds a single PartialView object from its array definition.
     *
     * @param string|int $key The identifier key for error reporting.
     * @param array $definition The array containing [template, data, partials].
     * @return PartialView The constructed PartialView object.
     */
    private function buildPartialFromArray($key, array $definition): PartialView
    {
        $nestedTemplate = $definition[0] ?? null;
        $nestedData = $definition[1] ?? [];
        $nestedPartials = $definition[2] ?? [];

        if (!is_string($nestedTemplate)) {
            throw new InvalidArgumentException("Invalid partial definition for key '{$key}'. Template file (index 0) must be a string.");
        }

        // Recursively call the main public method to build the nested partial.
        return $this->createPartialView(
            $nestedTemplate,
            $nestedData,
            $nestedPartials
        );
    }
}
