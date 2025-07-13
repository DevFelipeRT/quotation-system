<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component\Context;

use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\RenderContextInterface;

/**
 * A Data Transfer Object (DTO) that encapsulates the prepared context for rendering.
 *
 * It holds the final data array to be injected into the template and the
 * API context (the parent entity, such as a Page or Partial) for the ViewApi.
 */
final class RenderContext implements RenderContextInterface
{
    public function __construct(
        private readonly array $data,
        private readonly ?RenderableInterface $apiContext
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getApiContext(): ?RenderableInterface
    {
        return $this->apiContext;
    }
}
