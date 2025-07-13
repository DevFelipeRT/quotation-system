<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Factory;

use Rendering\Domain\Contract\RenderableDataInterface;
use Rendering\Domain\ValueObject\Shared\RenderableData;

class RenderableDataFactory
{
    /**
     * Builds a RenderableData object from an associative array.
     *
     * @param array $data The data to be wrapped in a RenderableData object.
     * @return RenderableDataInterface|null The constructed RenderableData object, or null if the data is empty.
     */
    public function createRenderableDataFromArray(array $data): ?RenderableDataInterface
    {
        if (empty($data)) {
            return null;
        }
        return new RenderableData($data);
    }
}